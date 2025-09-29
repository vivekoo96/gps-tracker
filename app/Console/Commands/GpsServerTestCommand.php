<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use React\Socket\SocketServer;
use React\Socket\ConnectionInterface;
use App\Models\Device;
use App\Models\GpsData;
use Carbon\Carbon;

class GpsServerTestCommand extends Command
{
    protected $signature = 'gps:test-server {port=5023} {--debug} {--log-raw}';
    protected $description = 'Start GPS server with R&D testing and debugging features';

    private $connections = [];
    private $messageCount = 0;
    private $startTime;

    public function handle()
    {
        $port = $this->argument('port');
        $debug = $this->option('debug');
        $logRaw = $this->option('log-raw');
        
        $this->startTime = now();
        
        $server = new SocketServer("0.0.0.0:$port");

        $this->info("🚀 GPS R&D Test Server Started");
        $this->info("📡 Port: $port");
        $this->info("🐛 Debug Mode: " . ($debug ? 'ON' : 'OFF'));
        $this->info("📝 Raw Logging: " . ($logRaw ? 'ON' : 'OFF'));
        $this->info("⏰ Started at: " . $this->startTime->format('Y-m-d H:i:s'));
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("🔍 Waiting for GPS devices to connect...");
        $this->info("💡 Test with: telnet localhost $port");
        $this->line("");

        $server->on('connection', function (ConnectionInterface $connection) use ($debug, $logRaw) {
            $remoteAddress = $connection->getRemoteAddress();
            $connectionId = uniqid();
            $this->connections[$connectionId] = [
                'connection' => $connection,
                'address' => $remoteAddress,
                'connected_at' => now(),
                'message_count' => 0
            ];

            $this->info("🔗 NEW CONNECTION");
            $this->line("   ID: $connectionId");
            $this->line("   From: $remoteAddress");
            $this->line("   Time: " . now()->format('H:i:s'));
            $this->line("   Total Connections: " . count($this->connections));
            $this->line("");

            $connection->on('data', function ($data) use ($connection, $connectionId, $debug, $logRaw) {
                $this->processGpsData($data, $connection, $connectionId, $debug, $logRaw);
            });

            $connection->on('close', function () use ($connectionId, $remoteAddress) {
                unset($this->connections[$connectionId]);
                $this->warn("❌ CONNECTION CLOSED");
                $this->line("   ID: $connectionId");
                $this->line("   From: $remoteAddress");
                $this->line("   Remaining: " . count($this->connections));
                $this->line("");
            });

            $connection->on('error', function ($error) use ($connectionId) {
                $this->error("💥 CONNECTION ERROR ($connectionId): " . $error->getMessage());
            });
        });

        $server->on('error', function ($e) {
            $this->error('🚨 SERVER ERROR: ' . $e->getMessage());
        });

        // Status reporting every 30 seconds
        $this->startStatusReporting();

        // Keep server running
        $this->info("🎯 Server is running... Press Ctrl+C to stop");
        while (true) {
            usleep(100000); // 0.1 second
        }
    }

    private function processGpsData($rawData, ConnectionInterface $connection, $connectionId, $debug, $logRaw)
    {
        $this->messageCount++;
        $this->connections[$connectionId]['message_count']++;
        
        $dataLength = strlen($rawData);
        $hexData = bin2hex($rawData);
        $asciiData = $this->sanitizeAscii($rawData);

        $this->info("📨 MESSAGE RECEIVED");
        $this->line("   Connection: $connectionId");
        $this->line("   Length: $dataLength bytes");
        $this->line("   Time: " . now()->format('H:i:s.v'));
        
        if ($logRaw) {
            $this->line("   HEX: $hexData");
            $this->line("   ASCII: $asciiData");
        }

        try {
            // Detect protocol type
            $protocol = $this->detectProtocol($rawData, $hexData);
            $this->line("   Protocol: $protocol");

            // Parse data based on protocol
            $parsedData = $this->parseByProtocol($rawData, $protocol, $debug);
            
            if ($parsedData) {
                $this->line("   ✅ PARSED SUCCESSFULLY");
                if ($debug) {
                    $this->displayParsedData($parsedData);
                }
                
                // Save to database
                $saved = $this->saveGpsData($parsedData);
                $this->line("   💾 Database: " . ($saved ? 'SAVED' : 'FAILED'));
                
                // Send acknowledgment
                $ack = $this->generateAck($parsedData, $protocol);
                if ($ack) {
                    $connection->write($ack);
                    $this->line("   📤 ACK Sent: " . bin2hex($ack));
                }
            } else {
                $this->warn("   ❌ PARSING FAILED");
                if ($debug) {
                    $this->line("   Raw HEX: $hexData");
                    $this->line("   Raw ASCII: $asciiData");
                }
            }
        } catch (\Exception $e) {
            $this->error("   💥 ERROR: " . $e->getMessage());
            if ($debug) {
                $this->error("   Stack: " . $e->getTraceAsString());
            }
        }
        
        $this->line("");
    }

    private function detectProtocol($rawData, $hexData)
    {
        // GT06N protocol detection
        if (substr($hexData, 0, 4) === '7878') {
            return 'GT06N';
        }
        
        // TK103 protocol detection (ASCII based)
        if (preg_match('/^(\(|\*)[A-Z0-9]+/', $rawData)) {
            return 'TK103';
        }
        
        // Teltonika protocol detection
        if (strlen($rawData) >= 4 && substr($hexData, 0, 8) === '00000000') {
            return 'Teltonika';
        }
        
        // Queclink protocol detection
        if (preg_match('/^\+[A-Z]+:/', $rawData)) {
            return 'Queclink';
        }
        
        return 'Unknown';
    }

    private function parseByProtocol($rawData, $protocol, $debug)
    {
        switch ($protocol) {
            case 'GT06N':
                return $this->parseGT06N($rawData, $debug);
            case 'TK103':
                return $this->parseTK103($rawData, $debug);
            case 'Teltonika':
                return $this->parseTeltonika($rawData, $debug);
            case 'Queclink':
                return $this->parseQueclink($rawData, $debug);
            default:
                return $this->parseGeneric($rawData, $debug);
        }
    }

    private function parseGT06N($rawData, $debug)
    {
        $hex = bin2hex($rawData);
        
        if ($debug) {
            $this->line("   🔍 GT06N Analysis:");
            $this->line("     Start Flag: " . substr($hex, 0, 4));
            $this->line("     Length: " . substr($hex, 4, 2));
            $this->line("     Protocol: " . substr($hex, 6, 2));
        }
        
        // Basic GT06N login packet
        if (substr($hex, 6, 2) === '01') {
            $imei = $this->extractIMEI($hex, 8, 16);
            return [
                'device_id' => $imei,
                'message_type' => 'login',
                'protocol' => 'GT06N',
                'timestamp' => now(),
                'raw_data' => $hex
            ];
        }
        
        // Location packet
        if (substr($hex, 6, 2) === '22') {
            return [
                'device_id' => $this->extractIMEI($hex, 8, 16),
                'latitude' => $this->extractLatitude($hex),
                'longitude' => $this->extractLongitude($hex),
                'speed' => $this->extractSpeed($hex),
                'protocol' => 'GT06N',
                'timestamp' => now(),
                'raw_data' => $hex
            ];
        }
        
        return null;
    }

    private function parseTK103($rawData, $debug)
    {
        $message = trim($rawData);
        
        if ($debug) {
            $this->line("   🔍 TK103 Analysis:");
            $this->line("     Message: $message");
        }
        
        // Login packet: (123456789012BR00)
        if (preg_match('/\((\d+)BR00\)/', $message, $matches)) {
            return [
                'device_id' => $matches[1],
                'message_type' => 'login',
                'protocol' => 'TK103',
                'timestamp' => now(),
                'raw_data' => $message
            ];
        }
        
        // Location packet
        if (preg_match('/(\d+),(\d{6}),([AV]),(\d+\.\d+),([NS]),(\d+\.\d+),([EW]),(\d+\.\d+),(\d+),(\d{6})/', $message, $matches)) {
            return [
                'device_id' => $matches[1],
                'latitude' => ($matches[4] / 100) * ($matches[5] == 'S' ? -1 : 1),
                'longitude' => ($matches[6] / 100) * ($matches[7] == 'W' ? -1 : 1),
                'speed' => floatval($matches[8]),
                'direction' => intval($matches[9]),
                'protocol' => 'TK103',
                'timestamp' => now(),
                'raw_data' => $message
            ];
        }
        
        return null;
    }

    private function parseTeltonika($rawData, $debug)
    {
        // Simplified Teltonika parsing
        return [
            'device_id' => 'teltonika_test',
            'protocol' => 'Teltonika',
            'timestamp' => now(),
            'raw_data' => bin2hex($rawData)
        ];
    }

    private function parseQueclink($rawData, $debug)
    {
        $parts = explode(',', trim($rawData));
        
        if (count($parts) >= 10) {
            return [
                'device_id' => $parts[2] ?? 'queclink_test',
                'latitude' => floatval($parts[6] ?? 0),
                'longitude' => floatval($parts[7] ?? 0),
                'speed' => floatval($parts[8] ?? 0),
                'protocol' => 'Queclink',
                'timestamp' => now(),
                'raw_data' => $rawData
            ];
        }
        
        return null;
    }

    private function parseGeneric($rawData, $debug)
    {
        return [
            'device_id' => 'unknown_device',
            'protocol' => 'Generic',
            'timestamp' => now(),
            'raw_data' => bin2hex($rawData)
        ];
    }

    private function displayParsedData($data)
    {
        $this->line("   📊 PARSED DATA:");
        foreach ($data as $key => $value) {
            if ($key !== 'raw_data') {
                $this->line("     $key: $value");
            }
        }
    }

    private function saveGpsData($data)
    {
        try {
            // Find or create device
            $device = Device::firstOrCreate(
                ['unique_id' => $data['device_id']],
                [
                    'name' => 'Auto-created: ' . $data['device_id'],
                    'device_type' => $data['protocol'] ?? 'Unknown',
                    'status' => 'active',
                    'creator' => 'System',
                    'account' => 'Auto'
                ]
            );

            // Save GPS data if location exists
            if (isset($data['latitude']) && isset($data['longitude'])) {
                GpsData::create([
                    'device_id' => $device->id,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'speed' => $data['speed'] ?? 0,
                    'direction' => $data['direction'] ?? 0,
                    'recorded_at' => $data['timestamp'],
                    'raw_data' => $data['raw_data']
                ]);
            }

            // Update device last seen
            $device->update(['last_seen_at' => now()]);
            
            return true;
        } catch (\Exception $e) {
            $this->error("Database error: " . $e->getMessage());
            return false;
        }
    }

    private function generateAck($data, $protocol)
    {
        switch ($protocol) {
            case 'GT06N':
                return pack('H*', '787805010001D9DC0D0A');
            case 'TK103':
                return "(" . $data['device_id'] . "AP01)";
            default:
                return null;
        }
    }

    private function startStatusReporting()
    {
        // This would need a proper async timer in a real implementation
        // For now, we'll just show status on each message
    }

    private function sanitizeAscii($data)
    {
        return preg_replace('/[^\x20-\x7E]/', '.', $data);
    }

    private function extractIMEI($hex, $start, $length)
    {
        return substr($hex, $start, $length);
    }

    private function extractLatitude($hex)
    {
        // Simplified extraction - implement proper GPS coordinate parsing
        return 0.0;
    }

    private function extractLongitude($hex)
    {
        // Simplified extraction - implement proper GPS coordinate parsing
        return 0.0;
    }

    private function extractSpeed($hex)
    {
        // Simplified extraction
        return 0;
    }
}
