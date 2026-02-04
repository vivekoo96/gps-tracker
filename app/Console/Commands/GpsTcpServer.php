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

        // CHECK PROTOCOL: GT06 (0x78 0x78) OR (0x79 0x79)
        if (str_starts_with($hex, '7878') || str_starts_with($hex, '7979')) {
            $isExtended = str_starts_with($hex, '7979');
            
            if ($isExtended) {
                // 79 79 [2 bytes Len] ... [2 bytes Stop]
                if ($len < 6) return;
                $pkgLen = hexdec(substr($hex, 4, 4)); 
                $totalExpectedBytes = 2 + 2 + $pkgLen + 2;
                $protocolOffset = 8;
            } else {
                // 78 78 [1 byte Len] ... [2 bytes Stop]
                if ($len < 5) return;
                $pkgLen = hexdec(substr($hex, 4, 2));
                $totalExpectedBytes = 2 + 1 + $pkgLen + 2;
                $protocolOffset = 6;
            }

            if ($len >= $totalExpectedBytes) {
                $packet = substr($buffer, 0, $totalExpectedBytes);
                $this->processGt06Packet($connection, $ctx, $packet, $isExtended);
                $ctx['buffer'] = substr($buffer, $totalExpectedBytes);
                
                if (strlen($ctx['buffer']) > 0) {
                    $this->processBuffer($connection, $ctx);
                }
            }
            return;
        }
        // ... (remaining buffer processing)
    }

    protected function processGt06Packet(ConnectionInterface $connection, array &$ctx, string $packet, bool $isExtended = false): void
    {
        $hex = bin2hex($packet);
        $protocolOffset = $isExtended ? 8 : 6;
        $protocolId = substr($hex, $protocolOffset, 2);

        switch ($protocolId) {
            case '01': // Login
                // Terminal ID is 8 bytes. Offset 4 bytes in 7878, 5 bytes in 7979
                $idOffset = $isExtended ? 10 : 8;
                $terminalIdHex = substr($hex, $idOffset, 16);
                $imeiCandidate = ltrim($terminalIdHex, '0');

                // Find Device (Bypass Eloquent)
                $deviceData = \Illuminate\Support\Facades\DB::table('devices')
                                ->where('unique_id', $terminalIdHex)
                                ->orWhere('imei', $terminalIdHex)
                                ->orWhere('imei', $imeiCandidate)
                                ->orWhere('unique_id', $imeiCandidate)
                                ->first();

                if ($deviceData) {
                    $ctx['device_id'] = $deviceData->id;
                    $ctx['device_name'] = $deviceData->name;
                    
                    \Illuminate\Support\Facades\DB::table('devices')
                        ->where('id', $deviceData->id)
                        ->update(['last_seen_at' => now(), 'status' => 'active']);
                        
                    $this->info("Login [GT06" . ($isExtended ? "-Ex" : "") . "]: {$deviceData->name} ({$terminalIdHex})");

                    // Standard Reply
                    $serial = substr($hex, -8, 4); 
                    $header = $isExtended ? "79790005" : "787805";
                    $resp = $header . "01" . $serial . "D9DC0D0A"; 
                    $connection->write(hex2bin($resp));
                } else {
                    $this->warn("Unknown Device Login: $terminalIdHex");
                }
                break;

            case '12': // Location
            case '22':
            case '94': // Info/Status often shared structure
                if (isset($ctx['device_id'])) {
                    // Generic GT06 Location Parsing (simplified offsets)
                    $dataStart = $isExtended ? 10 : 8;
                    
                    if (strlen($hex) < ($dataStart + 30)) return;

                    $latHex = substr($hex, $dataStart + 14, 8); 
                    $lonHex = substr($hex, $dataStart + 22, 8); 
                    $speedHex = substr($hex, $dataStart + 30, 2); 

                    $lat = hexdec($latHex) / 1800000.0;
                    $lon = hexdec($lonHex) / 1800000.0;
                    $speed = hexdec($speedHex);

                    if ($lat > 0 && $lon > 0) {
                        \Illuminate\Support\Facades\DB::table('positions')->insert([
                            'device_id' => $ctx['device_id'],
                            'latitude' => $lat,
                            'longitude' => $lon,
                            'speed' => $speed,
                            'fix_time' => now(),
                            'protocol' => $isExtended ? 'GT06-EX' : 'GT06',
                            'raw' => $hex,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        
                        \Illuminate\Support\Facades\DB::table('devices')
                            ->where('id', $ctx['device_id'])
                            ->update([
                                'last_seen_at' => now(),
                                'status' => 'active',
                                'last_location_update' => now(),
                                'latitude' => $lat,
                                'longitude' => $lon,
                                'speed' => $speed
                            ]);
                        
                        $this->info("GPS [{$ctx['device_name']}]: {$lat}, {$lon}");
                    }
                }
                break;

            case '13': // Heartbeat
                if (isset($ctx['device_id'])) {
                    \Illuminate\Support\Facades\DB::table('devices')
                        ->where('id', $ctx['device_id'])
                        ->update(['last_seen_at' => now()]);
                    
                    $serial = substr($hex, -8, 4);
                    $resp = ($isExtended ? "79790005" : "787805") . "13" . $serial . "D9DC0D0A";
                    $connection->write(hex2bin($resp));
                }
                break;
            
            case '22': // Alarm Data (often contains location too)
                // Similar to 12 but different offsets. For now treat as Heartbeat to keep alive.
                if ($ctx['device']) {
                     $ctx['device']->update(['last_seen_at' => now()]);
                     // Acknowledge
                     $serial = substr($hex, -8, 4);
                     $resp = "78780522" . $serial . "D9DC0D0A";
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


