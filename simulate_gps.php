<?php
/**
 * Stand-alone GPS Simulation Script
 * Simulates a G07 device (GT06 Protocol) Login
 */

$host = '127.0.0.1';
$port = 5010;
$imei = '869727072514837';

// Concatenate a leading 0 if 15 digits (to make 16 hex chars / 8 bytes)
$terminalId = str_pad($imei, 16, '0', STR_PAD_LEFT);

echo "Simulating GPS Connection for IMEI: {$imei}\n";
echo "Connecting to {$host}:{$port}...\n";

$socket = fsockopen($host, $port, $errno, $errstr, 5);

if (!$socket) {
    echo "ERROR: Could not connect: $errstr ($errno)\n";
    echo "Make sure the GPS server is running: php artisan gps:tcp-server\n";
    exit(1);
}

// Construct GT06 Login Packet
// Start: 78 78
// Length: 0D
// Protocol: 01 (Login)
// ID: [8 bytes]
// Serial: 00 01
// CRC: 12 34 (Dummy)
// Stop: 0D 0A
$packetHex = "78780d01" . $terminalId . "000112340d0a";
$packetBinary = hex2bin($packetHex);

echo "Sending Login Packet (Hex): {$packetHex}\n";
fwrite($socket, $packetBinary);

// Wait for Response
stream_set_timeout($socket, 2);
$response = fread($socket, 1024);

if ($response) {
    echo "Received Response (Hex): " . bin2hex($response) . "\n";
    if (str_contains(bin2hex($response), '78780501')) {
        echo "SUCCESS: Server accepted the login!\n";
        
        // --- SEND LOCATION PACKET ---
        echo "Sending Location Packet...\n";
        // 78 78 [Len] 12 [Data...] [Serial] [CRC] 0D 0A
        // For testing, let's use a real-looking location packet hex
        // Lat: 17.444, Lon: 78.333 (Hyderabad)
        // Lat Hex: 17.444 * 1800000 = 31399200 -> 01DF1920
        // Lon Hex: 78.333 * 1800000 = 140999400 -> 08677EE8
        $locationPacketHex = "78781f120e011400010001df192008677ee800000000000000000001000112340d0a";
        fwrite($socket, hex2bin($locationPacketHex));
        echo "Location sent: 17.444, 78.333\n";
        sleep(1);
    } else {
        echo "WARNING: Server replied with unexpected format.\n";
    }
} else {
    echo "ERROR: No response from server.\n";
}

fclose($socket);
