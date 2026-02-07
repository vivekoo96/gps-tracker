<?php

namespace App\Services\Protocols;

use App\Contracts\GpsProtocolParser;
use App\Models\Device;
use Illuminate\Support\Facades\Log;

class TextParser implements GpsProtocolParser
{
    public function canParse(string $data): bool
    {
        // Text parser is the fallback, accepts anything
        return true;
    }

    public function parse(string $data): array
    {
        $data = trim($data);
        
        $result = [
            'type' => 'unknown',
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
            'raw' => ['data' => $data],
        ];

        // Try to extract IMEI (15 digits)
        if (preg_match('/\b(\d{15})\b/', $data, $matches)) {
            $result['imei'] = $matches[1];
            $result['type'] = 'login';
        }

        // Try to extract coordinates
        // Look for patterns like: lat:12.3456 or latitude:12.3456
        if (preg_match('/lat(?:itude)?[:\s=]+(-?\d+\.?\d*)/', $data, $matches)) {
            $result['latitude'] = (float)$matches[1];
        }

        if (preg_match('/lon(?:gitude)?[:\s=]+(-?\d+\.?\d*)/', $data, $matches)) {
            $result['longitude'] = (float)$matches[1];
        }

        // If we found coordinates, it's a location packet
        if ($result['latitude'] !== null && $result['longitude'] !== null) {
            $result['type'] = 'location';
        }

        // Try to extract speed
        if (preg_match('/speed[:\s=]+(\d+\.?\d*)/', $data, $matches)) {
            $result['speed'] = (float)$matches[1];
        }

        Log::warning('Using text parser fallback', [
            'data' => $data,
            'parsed' => $result,
        ]);

        return $result;
    }

    public function buildLoginResponse(Device $device): string
    {
        return "OK\r\n";
    }

    public function buildHeartbeatResponse(): string
    {
        return "OK\r\n";
    }

    public function buildLocationResponse(): string
    {
        return "OK\r\n";
    }

    public function getProtocolName(): string
    {
        return 'text';
    }
}
