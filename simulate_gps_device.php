#!/usr/bin/env php
<?php

/**
 * GPS Device Simulator
 * 
 * This script simulates a real GPS device sending location data to your server.
 * Run this to test if your application can receive and display GPS data correctly.
 * 
 * Usage:
 *   php simulate_gps_device.php
 */

// Configuration
$serverUrl = 'http://gps.test/gps/data'; // Change to your server URL
$deviceId = '1234567890'; // Your device IMEI/ID
$updateInterval = 5; // Seconds between updates

// Starting location (Ahmedabad, India)
$latitude = 23.0225;
$longitude = 72.5714;
$speed = 0;
$heading = 0;

echo "🛰️  GPS Device Simulator Started\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Server URL: {$serverUrl}\n";
echo "Device ID: {$deviceId}\n";
echo "Update Interval: {$updateInterval} seconds\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$iteration = 0;

while (true) {
    $iteration++;
    
    // Simulate movement (random walk)
    $latitude += (rand(-10, 10) / 10000);
    $longitude += (rand(-10, 10) / 10000);
    $speed = rand(0, 80);
    $heading = rand(0, 360);
    $battery = rand(70, 100);
    $satellites = rand(6, 12);
    
    // Prepare GPS data
    $gpsData = [
        'device_id' => $deviceId,
        'latitude' => round($latitude, 6),
        'longitude' => round($longitude, 6),
        'speed' => round($speed, 2),
        'heading' => $heading,
        'altitude' => rand(50, 200),
        'satellites' => $satellites,
        'battery' => $battery,
        'timestamp' => date('c')
    ];
    
    // Send data to server
    $ch = curl_init($serverUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($gpsData));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Display status
    $timestamp = date('H:i:s');
    $status = $httpCode == 200 ? '✅' : '❌';
    
    echo "[{$timestamp}] Update #{$iteration} {$status}\n";
    echo "  📍 Location: {$gpsData['latitude']}, {$gpsData['longitude']}\n";
    echo "  🚗 Speed: {$speed} km/h | Heading: {$heading}°\n";
    echo "  🔋 Battery: {$battery}% | Satellites: {$satellites}\n";
    
    if ($httpCode == 200) {
        $responseData = json_decode($response, true);
        echo "  ✓ Server Response: " . ($responseData['message'] ?? 'Success') . "\n";
    } else {
        echo "  ✗ HTTP {$httpCode}: {$error}\n";
        if ($response) {
            echo "  Response: " . substr($response, 0, 100) . "\n";
        }
    }
    
    echo "\n";
    
    // Wait before next update
    sleep($updateInterval);
}
