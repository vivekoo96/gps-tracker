<?php

require_once __DIR__ . '/../GpsClient.php';

/**
 * Example: Real-time Fleet Monitor
 * 
 * This script demonstrates how to list all devices and check their current status.
 */

$config = [
    'base_url' => 'http://your-platform.com/api/v2',
    'api_key' => 'your_api_key_here',
    'api_secret' => 'your_api_secret_here'
];

$client = new GpsClient($config);

try {
    echo "--- Fleet Status Report ---\n";
    
    // 1. Get all devices
    $devices = $client->getDevices();
    
    foreach ($devices as $device) {
        $status = $device['status'] ?? 'unknown';
        $lastSeen = $device['last_seen'] ?? 'never';
        
        echo sprintf(
            "Device: %-15s | Status: %-10s | Last Seen: %s\n",
            $device['name'],
            strtoupper($status),
            $lastSeen
        );
        
        // 2. If device is online, get its last position
        if ($status === 'active') {
            $history = $client->getDeviceHistory($device['id'], [
                'limit' => 1
            ]);
            
            if (!empty($history)) {
                $pos = $history[0];
                echo sprintf("   📍 Position: %f, %f (Speed: %d km/h)\n", 
                    $pos['latitude'], 
                    $pos['longitude'], 
                    $pos['speed'] ?? 0
                );
            }
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
