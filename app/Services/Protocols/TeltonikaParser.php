<?php

namespace App\Services\Protocols;

use App\Contracts\GpsProtocolParser;
use App\Models\Device;
use Illuminate\Support\Facades\Log;

class TeltonikaParser implements GpsProtocolParser
{
    /**
     * Teltonika protocols usually start with 4 zero bytes
     * or it's a direct handshake.
     */
    public function canParse(string $data): bool
    {
        $hex = bin2hex($data);
        // Teltonika preamble is 4 bytes of zero
        return str_starts_with($hex, '00000000');
    }

    public function parse(string $data): array
    {
        $hex = bin2hex($data);
        $len = strlen($data);

        // Basic Teltonika Packet:
        // Preamble (4) | Data Length (4) | Codec ID (1) | Number of Data (1) | Data (X) | Number of Data (1) | CRC (4)

        if ($len < 15) {
            return ['type' => 'unknown', 'raw' => ['hex' => $hex]];
        }

        $dataLength = hexdec(substr($hex, 8, 8));
        $codecId = hexdec(substr($hex, 16, 2));
        $recordCount = hexdec(substr($hex, 18, 2));

        $result = [
            'type' => 'location',
            'imei' => null, // Teltonika normally sends IMEI in first packet without preamble
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
                'codec' => $codecId,
                'records' => $recordCount,
                'hex' => $hex,
            ],
        ];

        // If it's just the IMEI (handshake)
        if ($codecId > 0x0F && $len < 20) {
            $result['type'] = 'login';
            $result['imei'] = ltrim(hex2bin(substr($hex, 4)), "\x00"); 
            return $result;
        }

        // Parsing all records
        $records = [];
        $recordOffset = 20; // After Codec (1) and Count (1)
        
        for ($i = 0; $i < $recordCount; $i++) {
            $location = $this->parseRecord($hex, $recordOffset, $codecId);
            $records[] = $location;
            
            // In Codec 8, we skip I/O data for now to keep it simple but complete
            // 1B (Event) + 1B (N1) + N1*(1B ID + 1B Val) + 1B (N2) + N2*(1B ID + 2B Val) + ...
            $this->skipIOData($hex, $recordOffset, $codecId);
        }

        if (!empty($records)) {
            // Use the most recent record for the main result
            $latest = $records[0]; 
            $result = array_merge($result, $latest);
            $result['records'] = $records;
        }

        return $result;
    }

    protected function skipIOData(string $hex, int &$offset, int $codecId): void
    {
        // Event ID
        $offset += 2;
        
        // N of Total I/O
        $offset += 2;

        // N1 (1 byte)
        $n1 = hexdec(substr($hex, $offset, 2));
        $offset += 2 + ($n1 * 4); // ID (1) + Val (1)

        // N2 (2 bytes)
        $n2 = hexdec(substr($hex, $offset, 2));
        $offset += 2 + ($n2 * 6); // ID (1) + Val (2)

        // N4 (4 bytes)
        $n4 = hexdec(substr($hex, $offset, 2));
        $offset += 2 + ($n4 * 10); // ID (1) + Val (4)

        // N8 (8 bytes)
        $n8 = hexdec(substr($hex, $offset, 2));
        $offset += 2 + ($n8 * 18); // ID (1) + Val (8)
    }

    protected function parseRecord(string $hex, int &$offset, int $codecId): array
    {
        // Timestamp (8 bytes)
        $timestampMs = hexdec(substr($hex, $offset, 16));
        $timestamp = date('Y-m-d H:i:s', $timestampMs / 1000);
        $offset += 16;

        // Priority (1 byte)
        $offset += 2;

        // Longitude (4 bytes)
        $lonInt = $this->hexToSignedInt(substr($hex, $offset, 8));
        $longitude = $lonInt / 10000000.0;
        $offset += 8;

        // Latitude (4 bytes)
        $latInt = $this->hexToSignedInt(substr($hex, $offset, 8));
        $latitude = $latInt / 10000000.0;
        $offset += 8;

        // Altitude (2 bytes)
        $altitude = hexdec(substr($hex, $offset, 4));
        $offset += 4;

        // Angle (2 bytes)
        $course = hexdec(substr($hex, $offset, 4));
        $offset += 4;

        // Satellites (1 byte)
        $satellites = hexdec(substr($hex, $offset, 2));
        $offset += 2;

        // Speed (2 bytes)
        $speed = hexdec(substr($hex, $offset, 4));
        $offset += 4;

        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed' => $speed,
            'course' => $course,
            'altitude' => $altitude,
            'satellites' => $satellites,
            'timestamp' => $timestamp,
        ];
    }

    protected function hexToSignedInt($hex): int
    {
        $val = hexdec($hex);
        if ($val >= 0x80000000) {
            $val -= 0x100000000;
        }
        return (int)$val;
    }

    public function buildLoginResponse(Device $device): string
    {
        // Teltonika login response is 01
        return "\x01";
    }

    public function buildHeartbeatResponse(): string
    {
        // Teltonika response: Number of records (4 bytes)
        return pack('N', 1);
    }

    public function buildLocationResponse(): string
    {
        return $this->buildHeartbeatResponse();
    }

    public function getProtocolName(): string
    {
        return 'teltonika';
    }
}
