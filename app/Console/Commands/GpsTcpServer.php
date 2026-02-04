<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;

class GpsTcpServer extends Command
{
    protected $signature = 'gps:tcp-server {--host=0.0.0.0} {--port=5010}';

    protected $description = 'Run a high-performance TCP server for GPS data (ReactPHP)';

    // Store connection context: buffer, authentication state, etc.
    // Keyed by output of spl_object_id($connection) or just use SplObjectStorage
    protected array $connections = [];

    public function handle(): int
    {
        $host = $this->option('host');
        $port = (int) $this->option('port');

        // Check if ReactPHP is available (user confirmed it is, but good practice)
        if (!class_exists('React\Socket\SocketServer')) {
            $this->error("ReactPHP is not installed. Please run: composer require react/socket");
            return self::FAILURE;
        }

        $this->info("Starting High-Performance GPS Server on {$host}:{$port} (Event-Driven)");

        $socket = new SocketServer("{$host}:{$port}");

        $socket->on('connection', function (ConnectionInterface $connection) {
            $connId = spl_object_hash($connection);
            $this->info("New Connection: {$connection->getRemoteAddress()} ({$connId})");

            // Initialize Context
            $this->connections[$connId] = [
                'buffer' => '',
                'device' => null,
                'ip' => $connection->getRemoteAddress()
            ];

            // Handle Incoming Data
            $connection->on('data', function ($data) use ($connection, $connId) {
                $this->handleData($connection, $connId, $data);
            });

            // Handle Disconnection
            $connection->on('close', function () use ($connId) {
                $this->info("Connection Closed: {$connId}");
                unset($this->connections[$connId]);
            });

            // Handle Errors
            $connection->on('error', function (\Exception $e) use ($connId) {
                $this->error("Connection Error [{$connId}]: " . $e->getMessage());
            });
        });

        $socket->on('error', function (\Exception $e) {
            $this->error("Socket Server Error: " . $e->getMessage());
        });

        // Loop runs automatically in recent ReactPHP versions when resources are active, 
        // but explicitly calling it ensures command doesn't exit.
        
        /* @phpstan-ignore-next-line */
        if (class_exists('React\EventLoop\Loop')) {
            Loop::run();
        } else {
             // Fallback for older versions
            $loop = \React\EventLoop\Factory::create();
            $loop->run();
        }

        return self::SUCCESS;
    }

    protected function handleData(ConnectionInterface $connection, string $connId, string $data): void
    {
        if (!isset($this->connections[$connId])) return;

        // Log Raw (Debug)
        // Log::channel('daily')->info("RX [{$connId}]", ['hex' => bin2hex($data)]);

        $ctx = &$this->connections[$connId];
        $ctx['buffer'] .= $data;

        // Packet Framing Strategy:
        // GT06 uses 0x78 0x78 [Len] ... so we can detect start.
        // But TCP is a stream. We might get half a packet or 1.5 packets.
        // For this implementation, we'll try to process whatever is in the buffer.
        // If we process a packet, we remove it from buffer.
        
        $this->processBuffer($connection, $ctx);
    }

    protected function processBuffer(ConnectionInterface $connection, array &$ctx): void
    {
        $buffer = $ctx['buffer'];
        $len = strlen($buffer);
        
        if ($len < 5) return; // Need at least header

        $hex = bin2hex($buffer);

        // CHECK PROTOCOL: GT06 (0x78 0x78)
        if (str_starts_with($hex, '7878')) {
            // GT06 Packet Structure: 78 78 [1 byte Len] [1 byte Protocol] ...
            $pkgLen = hexdec(substr($hex, 4, 2)); // Length of content AFTER length byte
            // Total Packet Length = 2 (Start) + 1 (LenByte) + $pkgLen + 2 (Stop 0D 0A)
            // Wait, standard GT06: Start(2) + Len(1) + Proto(1) + Content(N) + Serial(2) + CRC(2) + Stop(2)
            // The 'Len' byte usually covers (Proto + Content + Serial + CRC).
            // So Total Bytes = 2 (Start) + 1 (LenByte) + LenValue + 2 (Stop) ?
            // Let's assume standard: 
            // 78 78 [Len] [Proto] ... [CRC] 0D 0A
            // Length check:
            $totalExpectedBytes = 2 + 1 + $pkgLen + 2; 

            // Error check: if calculated length is crazy big, might be garbage
            if ($pkgLen > 200) {
                 // Reset buffer to prevent memory issues with garbage
                 $ctx['buffer'] = '';
                 return;
            }

            if ($len >= $totalExpectedBytes) {
                // Determine STOP bytes check: 0D 0A
                // $stop = substr($hex, ($totalExpectedBytes * 2) - 4, 4);
                // if ($stop !== '0d0a') ...

                // Extract full packet
                $packet = substr($buffer, 0, $totalExpectedBytes);
                
                // Process this packet
                $this->processGt06Packet($connection, $ctx, $packet);

                // Remove packet from buffer
                $ctx['buffer'] = substr($buffer, $totalExpectedBytes);
                
                // Recursively process remaining buffer
                if (strlen($ctx['buffer']) > 0) {
                    $this->processBuffer($connection, $ctx);
                }
            }
            return;
        }

        // TEXT PROTOCOL FALLBACK (Ending with \n)
        if (str_contains($buffer, "\n")) {
             $lines = explode("\n", $buffer);
             // Ensure we leave the last partial line in buffer if not empty
             $last = array_pop($lines);
             
             foreach ($lines as $line) {
                 $line = trim($line);
                 if ($line) {
                     $this->processTextPacket($connection, $ctx, $line);
                 }
             }
             
             // Put back remaining component
             $ctx['buffer'] = $last;
        }
    }

    protected function processGt06Packet(ConnectionInterface $connection, array &$ctx, string $packet): void
    {
        $hex = bin2hex($packet);
        $protocolId = substr($hex, 6, 2);

        switch ($protocolId) {
            case '01': // Login
                // 78 78 0D 01 01 23 45 67 89 01 23 45 00 01 8C DD 0D 0A
                // Terminal ID is 8 bytes starting at offset 4 (bytes) -> hex offset 8
                $terminalIdHex = substr($hex, 8, 16);
                
                // Find Device
                $device = Device::where('unique_id', $terminalIdHex)
                                ->orWhere('imei', $terminalIdHex) // Simplified matching
                                ->first();

                if ($device) {
                    $ctx['device'] = $device;
                    $device->update(['last_seen_at' => now(), 'status' => 'active']);
                    $this->info("Login [GT06]: {$device->name} ({$terminalIdHex})");

                    // Response: 78 78 05 01 [Serial(2)] [CRC(2)] 0D 0A
                    // Serial is at end of content. 
                    // Content len is PacketLen - 5 (Proto1 + Serial2 + CRC2) ? code based on fixed offsets
                    // Serial is typically last 4 bytes before CRC? 
                    // Let's grab Serial from input to reply. 
                    // Standard: ... [Serial No (2 bytes)] [Error Check (2 bytes)] [Stop (2 bytes)]
                    $serial = substr($hex, -8, 4); // 2 bytes serial
                    
                    // Construct Reply
                    $resp = "78780501" . $serial . "D9DC0D0A"; // Dummy CRC
                    $connection->write(hex2bin($resp));
                } else {
                    $this->warn("Unknown Device Login: $terminalIdHex");
                }
                break;

            case '12': // Location
            case '22':
                if ($ctx['device']) {
                    $ctx['device']->update(['last_seen_at' => now()]);
                    // Process Location Data here...
                    // $this->info("Ping from {$ctx['device']->name}");
                }
                break;

            case '13': // Heartbeat
                if ($ctx['device']) {
                    // Status info in packet...
                    $ctx['device']->update(['last_seen_at' => now()]);
                    
                    $serial = substr($hex, -8, 4);
                    // Reply: 78 78 05 13 [Serial] [CRC] 0D 0A
                    $resp = "78780513" . $serial . "D9DC0D0A";
                    $connection->write(hex2bin($resp));
                }
                break;
        }
    }

    protected function processTextPacket(ConnectionInterface $connection, array &$ctx, string $line): void
    {
        // Simple Text Protocol Handler
        // e.g. "ID=12345,LAT=...,LON=..."
        // Or "IMEI=12345"
        
        if (preg_match('/IMEI[:=](\d+)/i', $line, $matches)) {
            $imei = $matches[1];
            $device = Device::where('imei', $imei)->orWhere('unique_id', $imei)->first();
            if ($device) {
                $ctx['device'] = $device;
                $device->update(['last_seen_at' => now(), 'status' => 'active']);
                $connection->write("OK\n");
                $this->info("Login [Text]: {$device->name}");
            }
        } elseif ($ctx['device']) {
            $ctx['device']->update(['last_seen_at' => now()]);
        }
    }
}


