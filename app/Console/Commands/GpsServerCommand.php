<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use React\Socket\SocketServer;
use React\Socket\ConnectionInterface;
use App\Models\Device;
use App\Models\GpsData;
use Carbon\Carbon;

class GpsServerCommand extends Command
{
    protected $signature = 'gps:server {port=5023}';
    protected $description = 'Start GPS tracking server';

    public function handle()
    {
        $port = $this->argument('port');
        $server = new SocketServer("0.0.0.0:$port");

        $this->info("GPS Server started on port $port");
        $this->info("Waiting for GPS devices to connect...");

        $server->on('connection', function (ConnectionInterface $connection) use ($port) {
            $remoteAddress = $connection->getRemoteAddress();
            $this->info("New connection from: $remoteAddress");

            $connection->on('data', function ($data) use ($connection, $port) {
                $this->processGpsData($data, $connection, $port);
            });

            $connection->on('close', function () use ($remoteAddress) {
                $this->info("Connection closed: $remoteAddress");
            });
        });

        $server->on('error', function (\Throwable $e) {
            $this->error('Server error: ' . $e->getMessage());
        });

        // Keep the server running with ReactPHP event loop
        \React\EventLoop\Loop::run();
    }

    private function processGpsData($rawData, ConnectionInterface $connection, $port)
    {
        try {
            $this->info("Received data: " . bin2hex($rawData));
            
            // Parse different GPS protocols based on port
            $parsedData = $this->parseGpsProtocol($rawData, $port);
            
            if ($parsedData) {
                $this->saveGpsData($parsedData);
                $this->info("GPS data saved for device: " . $parsedData['device_id']);
                
                // Send acknowledgment back to device
                $ack = $this->generateAcknowledgment($parsedData, $port);
                if ($ack) {
                    $connection->write($ack);
                }
            }
        } catch (Exception $e) {
            $this->error("Error processing GPS data: " . $e->getMessage());
        }
    }

    private function parseGpsProtocol($data, $port)
    {
        // Different parsing logic based on port/protocol
        switch ($port) {
            case 5023: // GT06N protocol
                return $this->parseGT06Protocol($data);
            case 8082: // TK103 protocol
                return $this->parseTK103Protocol($data);
            case 5027: // Teltonika protocol
                return $this->parseTeltonikaProtocol($data);
            case 6001: // Queclink protocol
                return $this->parseQueclinkProtocol($data);
            default:
                return $this->parseGenericProtocol($data);
        }
    }

    private function parseGT06Protocol($data)
    {
        // GT06N protocol parsing
        $hex = bin2hex($data);
        
        // Basic GT06N packet structure
        if (strlen($hex) < 20) return null;
        
        // Extract device ID (IMEI)
        $deviceId = $this->extractDeviceId($hex);
        
        // Extract GPS coordinates
        $latitude = $this->extractLatitude($hex);
        $longitude = $this->extractLongitude($hex);
        
        // Extract other data
        $speed = $this->extractSpeed($hex);
        $direction = $this->extractDirection($hex);
        
        return [
            'device_id' => $deviceId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed' => $speed,
            'direction' => $direction,
            'timestamp' => now(),
            'raw_data' => $hex
        ];
    }

    private function parseTK103Protocol($data)
    {
        // TK103 protocol parsing
        $message = trim($data);
        
        // TK103 sends ASCII messages
        if (strpos($message, 'BR00') !== false) {
            // Login packet
            preg_match('/BR00(\d+)/', $message, $matches);
            return [
                'device_id' => $matches[1] ?? null,
                'message_type' => 'login',
                'timestamp' => now()
            ];
        }
        
        // Parse location data
        if (preg_match('/(\d+),(\d{6}),([AV]),(\d+\.\d+),([NS]),(\d+\.\d+),([EW]),(\d+\.\d+),(\d+),(\d{6})/', $message, $matches)) {
            return [
                'device_id' => $matches[1],
                'latitude' => ($matches[4] / 100) * ($matches[5] == 'S' ? -1 : 1),
                'longitude' => ($matches[6] / 100) * ($matches[7] == 'W' ? -1 : 1),
                'speed' => $matches[8],
                'direction' => $matches[9],
                'timestamp' => now(),
                'raw_data' => $message
            ];
        }
        
        return null;
    }

    private function parseTeltonikaProtocol($data)
    {
        // Teltonika protocol parsing (binary)
        // This is a simplified version - Teltonika protocol is complex
        $hex = bin2hex($data);
        
        // Basic Teltonika parsing
        return [
            'device_id' => $this->extractTeltonikaIMEI($hex),
            'latitude' => $this->extractTeltonikaLat($hex),
            'longitude' => $this->extractTeltonikaLng($hex),
            'timestamp' => now(),
            'raw_data' => $hex
        ];
    }

    private function parseQueclinkProtocol($data)
    {
        // Queclink protocol parsing
        $message = trim($data);
        
        // Queclink sends formatted ASCII
        $parts = explode(',', $message);
        
        if (count($parts) >= 10) {
            return [
                'device_id' => $parts[2] ?? null,
                'latitude' => floatval($parts[6] ?? 0),
                'longitude' => floatval($parts[7] ?? 0),
                'speed' => floatval($parts[8] ?? 0),
                'direction' => floatval($parts[9] ?? 0),
                'timestamp' => now(),
                'raw_data' => $message
            ];
        }
        
        return null;
    }

    private function saveGpsData($data)
    {
        // Find device by IMEI/ID
        $device = Device::where('unique_id', $data['device_id'])
                       ->orWhere('imei', $data['device_id'])
                       ->first();
        
        if (!$device) {
            $this->warn("Device not found: " . $data['device_id']);
            return;
        }

        // Save GPS data
        GpsData::create([
            'device_id' => $device->id,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'speed' => $data['speed'] ?? 0,
            'direction' => $data['direction'] ?? 0,
            'altitude' => $data['altitude'] ?? 0,
            'satellites' => $data['satellites'] ?? 0,
            'battery_level' => $data['battery'] ?? null,
            'signal_strength' => $data['signal'] ?? null,
            'recorded_at' => $data['timestamp'],
            'raw_data' => $data['raw_data'] ?? null
        ]);

        // Update device last seen
        $device->update([
            'last_seen_at' => now(),
            'status' => 'active'
        ]);
    }

    private function generateAcknowledgment($data, $port)
    {
        // Generate protocol-specific acknowledgment
        switch ($port) {
            case 5023: // GT06N
                return pack('H*', '787805010001D9DC0D0A');
            case 8082: // TK103
                return "(" . $data['device_id'] . "AP01)";
            default:
                return null;
        }
    }

    // Helper methods for GT06N data extraction
    private function extractDeviceId($hex)
    {
        // GT06N IMEI is typically in BCD format starting at byte 4
        // Extract 8 bytes (16 hex chars) for IMEI
        if (strlen($hex) < 24) return 'unknown';
        
        $imeiHex = substr($hex, 8, 16);
        // Convert BCD to decimal IMEI
        $imei = '';
        for ($i = 0; $i < strlen($imeiHex); $i += 2) {
            $imei .= substr($imeiHex, $i, 2);
        }
        return ltrim($imei, '0') ?: 'unknown';
    }

    private function extractLatitude($hex)
    {
        // GT06N location packet: latitude starts around byte 11 (after header + time)
        // Typical position in location packet (0x22)
        if (strlen($hex) < 60) return 0.0;
        
        // Extract 4 bytes for latitude (32-bit value)
        $latHex = substr($hex, 22, 8);
        $latInt = hexdec($latHex);
        
        // GT06N format: latitude = value / 1800000.0
        $latitude = $latInt / 1800000.0;
        
        // Check hemisphere bit and adjust if needed
        return round($latitude, 6);
    }

    private function extractLongitude($hex)
    {
        // Longitude follows latitude
        if (strlen($hex) < 70) return 0.0;
        
        // Extract 4 bytes for longitude (32-bit value)
        $lngHex = substr($hex, 30, 8);
        $lngInt = hexdec($lngHex);
        
        // GT06N format: longitude = value / 1800000.0
        $longitude = $lngInt / 1800000.0;
        
        return round($longitude, 6);
    }

    private function extractSpeed($hex)
    {
        // Speed is typically 1 byte
        if (strlen($hex) < 40) return 0;
        
        $speedHex = substr($hex, 38, 2);
        return hexdec($speedHex);
    }

    private function extractDirection($hex)
    {
        // Direction/course is typically 2 bytes
        if (strlen($hex) < 44) return 0;
        
        $dirHex = substr($hex, 40, 4);
        return hexdec($dirHex) & 0x03FF; // Mask to get direction value (0-360)
    }

    // Helper methods for Teltonika data extraction
    private function extractTeltonikaIMEI($hex)
    {
        // Teltonika IMEI is sent in initial packet
        // Simplified extraction - needs full protocol implementation
        if (strlen($hex) < 30) return 'teltonika_device';
        
        // IMEI is 15 digits, often sent as ASCII or BCD
        $imeiHex = substr($hex, 8, 30);
        return 'teltonika_' . substr($imeiHex, 0, 15);
    }

    private function extractTeltonikaLat($hex)
    {
        // Teltonika uses 4-byte signed integer for coordinates
        // Value = degrees * 10000000
        if (strlen($hex) < 100) return 0.0;
        
        $latHex = substr($hex, 60, 8);
        $latInt = hexdec($latHex);
        
        // Convert to signed integer if needed
        if ($latInt > 0x7FFFFFFF) {
            $latInt = $latInt - 0x100000000;
        }
        
        return round($latInt / 10000000.0, 6);
    }

    private function extractTeltonikaLng($hex)
    {
        // Longitude follows latitude in Teltonika protocol
        if (strlen($hex) < 108) return 0.0;
        
        $lngHex = substr($hex, 68, 8);
        $lngInt = hexdec($lngHex);
        
        // Convert to signed integer if needed
        if ($lngInt > 0x7FFFFFFF) {
            $lngInt = $lngInt - 0x100000000;
        }
        
        return round($lngInt / 10000000.0, 6);
    }
}
