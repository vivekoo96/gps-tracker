<?php

namespace App\Services\Protocols;

use App\Contracts\GpsProtocolParser;
use App\Models\Device;
use Illuminate\Support\Facades\Log;

class QueclinkParser implements GpsProtocolParser
{
    public function canParse(string $data): bool
    {
        // Queclink usually starts with +RESP:GT or +ACK:GT or +SACK:GT
        return str_starts_with($data, '+RESP:GT') || 
               str_starts_with($data, '+ACK:GT') || 
               str_starts_with($data, '+SACK:GT') ||
               str_starts_with($data, '+BUFF:GT');
    }

    public function parse(string $data): array
    {
        $parts = explode(',', trim($data));
        $header = $parts[0] ?? ''; // e.g. +RESP:GTFRI
        
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
            'raw' => [
                'header' => $header,
                'parts' => $parts,
            ],
        ];

        if (count($parts) < 3) return $result;

        // Protocol Type (e.g. GTFRI, GTRTO, etc)
        $protoType = substr($header, 6);
        $result['imei'] = $parts[2];

        // Check for location packets
        if (in_array($protoType, ['GTFRI', 'GTRTO', 'GTINF', 'GTPNA', 'GTDIS', 'GTIOB', 'GTEVT', 'GTSTT'])) {
            $result['type'] = 'location';
            
            // Queclink usually has location data starting from index 8-12 depending on message
            // We'll try to find the one with GPS Time (YYYYMMDDHHMMSS)
            foreach ($parts as $idx => $val) {
                if (strlen($val) === 14 && is_numeric($val) && str_starts_with($val, '20')) {
                    // Likely the timestamp
                    $result['timestamp'] = sprintf('%s-%s-%s %s:%s:%s',
                        substr($val, 0, 4), substr($val, 4, 2), substr($val, 6, 2),
                        substr($val, 8, 2), substr($val, 10, 2), substr($val, 12, 2)
                    );
                    
                    // Positions are usually just before/after the timestamp
                    if (isset($parts[$idx-1]) && isset($parts[$idx-2])) {
                        $result['latitude'] = (float)$parts[$idx-1];
                        $result['longitude'] = (float)$parts[$idx-2];
                    }
                    
                    // Speed/Course are often a few indices before
                    $result['speed'] = (float)($parts[$idx-5] ?? 0);
                    $result['course'] = (float)($parts[$idx-4] ?? 0);
                    break;
                }
            }
        } elseif (str_contains($header, 'ACK')) {
            $result['type'] = 'heartbeat';
        } elseif (str_contains($header, 'INF')) {
            $result['type'] = 'login';
        }

        return $result;
    }

    public function buildLoginResponse(Device $device): string
    {
        // Queclink usually doesn't strictly need a binary ACK for INF if it's over TCP, 
        // but some models want it.
        return "+ACK:GTINF,0001$"; 
    }

    public function buildHeartbeatResponse(): string
    {
        return "";
    }

    public function buildLocationResponse(): string
    {
        // Queclink sometimes expects an ACK for RESP: messages
        return "";
    }

    public function getProtocolName(): string
    {
        return 'queclink';
    }
}
