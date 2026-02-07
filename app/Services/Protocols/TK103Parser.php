<?php

namespace App\Services\Protocols;

use App\Contracts\GpsProtocolParser;
use App\Models\Device;
use Illuminate\Support\Facades\Log;

class TK103Parser implements GpsProtocolParser
{
    public function canParse(string $data): bool
    {
        // TK103 uses parentheses format: (IMEI...) or (IMEI BP05...)
        return str_starts_with($data, '(') && str_contains($data, ')');
    }

    public function parse(string $data): array
    {
        $data = trim($data);
        
        // Remove parentheses
        $data = trim($data, '()');
        
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

        // Check if it's just IMEI (login)
        if (strlen($data) <= 15 && ctype_digit($data)) {
            $result['type'] = 'login';
            $result['imei'] = $data;
            return $result;
        }

        // Parse location data
        // Format: IMEI BP05 timestamp A/V lat N/S lon E/W speed course date status
        $parts = explode(',', $data);
        
        if (count($parts) < 2) {
            return $result;
        }

        // First part is IMEI + command
        $firstPart = $parts[0];
        if (preg_match('/^(\d{15})(BP\d{2})/', $firstPart, $matches)) {
            $result['imei'] = $matches[1];
            $command = $matches[2];
            
            if ($command === 'BP05') {
                $result['type'] = 'location';
            } elseif ($command === 'BP00') {
                $result['type'] = 'heartbeat';
            }
        }

        // Parse location if available
        if (count($parts) >= 10) {
            // Parts: [0]=IMEI+cmd, [1]=timestamp, [2]=A/V, [3]=lat, [4]=N/S, [5]=lon, [6]=E/W, [7]=speed, [8]=date, [9]=status
            
            $valid = $parts[2] === 'A'; // A = valid, V = invalid
            
            if ($valid) {
                // Latitude: DDMM.MMMM format
                $latStr = $parts[3];
                $latDeg = (int)substr($latStr, 0, 2);
                $latMin = (float)substr($latStr, 2);
                $latitude = $latDeg + ($latMin / 60);
                if ($parts[4] === 'S') {
                    $latitude = -$latitude;
                }
                
                // Longitude: DDDMM.MMMM format
                $lonStr = $parts[5];
                $lonDeg = (int)substr($lonStr, 0, 3);
                $lonMin = (float)substr($lonStr, 3);
                $longitude = $lonDeg + ($lonMin / 60);
                if ($parts[6] === 'W') {
                    $longitude = -$longitude;
                }
                
                $result['latitude'] = $latitude;
                $result['longitude'] = $longitude;
                $result['speed'] = isset($parts[7]) ? (float)$parts[7] : null;
                
                // Parse timestamp if available
                if (isset($parts[1]) && isset($parts[8])) {
                    $time = $parts[1]; // HHMMSS
                    $date = $parts[8]; // DDMMYY
                    
                    if (strlen($time) === 6 && strlen($date) === 6) {
                        $hour = substr($time, 0, 2);
                        $minute = substr($time, 2, 2);
                        $second = substr($time, 4, 2);
                        $day = substr($date, 0, 2);
                        $month = substr($date, 2, 2);
                        $year = '20' . substr($date, 4, 2);
                        
                        $result['timestamp'] = "{$year}-{$month}-{$day} {$hour}:{$minute}:{$second}";
                    }
                }
            }
        }

        return $result;
    }

    public function buildLoginResponse(Device $device): string
    {
        // TK103 login response: (LOAD)
        return "(LOAD)\r\n";
    }

    public function buildHeartbeatResponse(): string
    {
        // TK103 heartbeat response: (AP01)
        return "(AP01)\r\n";
    }

    public function buildLocationResponse(): string
    {
        // TK103 location response: (AP05)
        return "(AP05)\r\n";
    }

    public function getProtocolName(): string
    {
        return 'tk103';
    }
}
