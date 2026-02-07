<?php

namespace App\Services\Protocols;

use App\Contracts\GpsProtocolParser;
use App\Models\Device;
use Illuminate\Support\Facades\Log;

class GT06Parser implements GpsProtocolParser
{
    public function canParse(string $data): bool
    {
        $hex = bin2hex($data);
        // GT06 starts with 0x7878 or 0x7979
        return str_starts_with($hex, '7878') || str_starts_with($hex, '7979');
    }

    public function parse(string $data): array
    {
        $hex = bin2hex($data);
        $isExtended = str_starts_with($hex, '7979');
        
        $protocolOffset = $isExtended ? 8 : 6;
        $protocol = hexdec(substr($hex, $protocolOffset, 2));

        $result = [
            'type' => $this->getPacketType($protocol),
            'imei' => null,
            'latitude' => null,
            'longitude' => null,
            'speed' => null,
            'course' => null,
            'altitude' => null,
            'satellites' => null,
            'timestamp' => null,
            'battery' => null,
            'signal' => null,
            'raw' => [
                'protocol' => $protocol,
                'is_extended' => $isExtended,
                'hex' => $hex,
            ],
        ];

        // Parse based on packet type
        switch ($protocol) {
            case 0x01: // Login
                $result['imei'] = $this->parseLoginPacket($hex, $isExtended);
                break;
            
            case 0x12: // Location
            case 0x22: // Location with LBS
                $locationData = $this->parseLocationPacket($hex, $isExtended);
                $result = array_merge($result, $locationData);
                break;
            
            case 0x13: // Heartbeat
                // Heartbeat doesn't contain much data
                break;
            
            case 0x16: // Alarm
                $locationData = $this->parseLocationPacket($hex, $isExtended);
                $result = array_merge($result, $locationData);
                $result['type'] = 'alarm';
                break;
        }

        return $result;
    }

    protected function getPacketType(int $protocol): string
    {
        return match($protocol) {
            0x01 => 'login',
            0x12, 0x22 => 'location',
            0x13 => 'heartbeat',
            0x16 => 'alarm',
            default => 'unknown',
        };
    }

    protected function parseLoginPacket(string $hex, bool $isExtended): ?string
    {
        $offset = $isExtended ? 8 : 6;
        // IMEI is 8 bytes after protocol number
        $imeiHex = substr($hex, $offset + 2, 16);
        return $imeiHex;
    }

    protected function parseLocationPacket(string $hex, bool $isExtended): array
    {
        $offset = $isExtended ? 10 : 8; // After start + length + protocol
        
        // Parse date/time (6 bytes)
        $year = 2000 + hexdec(substr($hex, $offset, 2));
        $month = hexdec(substr($hex, $offset + 2, 2));
        $day = hexdec(substr($hex, $offset + 4, 2));
        $hour = hexdec(substr($hex, $offset + 6, 2));
        $minute = hexdec(substr($hex, $offset + 8, 2));
        $second = hexdec(substr($hex, $offset + 10, 2));
        
        $timestamp = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second);
        
        $offset += 12; // Move past datetime
        
        // Parse GPS data (9 bytes)
        $satellites = hexdec(substr($hex, $offset, 2)) & 0x0F;
        $offset += 2;
        
        // Latitude (4 bytes)
        $latHex = substr($hex, $offset, 8);
        $latInt = hexdec($latHex);
        $latitude = $latInt / 1800000.0;
        $offset += 8;
        
        // Longitude (4 bytes)
        $lonHex = substr($hex, $offset, 8);
        $lonInt = hexdec($lonHex);
        $longitude = $lonInt / 1800000.0;
        $offset += 8;
        
        // Speed (1 byte)
        $speed = hexdec(substr($hex, $offset, 2));
        $offset += 2;
        
        // Course/Status (2 bytes)
        $courseStatus = hexdec(substr($hex, $offset, 4));
        $course = $courseStatus & 0x03FF;
        
        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed' => $speed,
            'course' => $course,
            'satellites' => $satellites,
            'timestamp' => $timestamp,
        ];
    }

    public function buildLoginResponse(Device $device): string
    {
        // GT06 login response: 78 78 05 01 [serial] [CRC] 0D 0A
        $serial = pack('n', 0x0001); // Serial number
        $response = "\x78\x78\x05\x01" . $serial;
        
        // Calculate CRC
        $crc = $this->calculateCRC($response);
        $response .= pack('n', $crc) . "\x0D\x0A";
        
        return $response;
    }

    public function buildHeartbeatResponse(): string
    {
        // GT06 heartbeat response: 78 78 05 13 [serial] [CRC] 0D 0A
        $serial = pack('n', 0x0001);
        $response = "\x78\x78\x05\x13" . $serial;
        
        $crc = $this->calculateCRC($response);
        $response .= pack('n', $crc) . "\x0D\x0A";
        
        return $response;
    }

    public function buildLocationResponse(): string
    {
        // GT06 location response: same as heartbeat
        return $this->buildHeartbeatResponse();
    }

    protected function calculateCRC(string $data): int
    {
        $crc = 0;
        $len = strlen($data);
        
        for ($i = 2; $i < $len; $i++) {
            $crc ^= ord($data[$i]);
        }
        
        return $crc;
    }

    public function getProtocolName(): string
    {
        return 'gt06';
    }
}
