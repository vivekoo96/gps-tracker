<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use React\Socket\Connector;
use React\Stream\WritableResourceStream;

class GpsSimulatorCommand extends Command
{
    protected $signature = 'gps:simulate {host=localhost} {port=5023} {--device=TK103} {--imei=123456789012345} {--interval=10} {--count=10}';
    protected $description = 'Simulate GPS device sending data to test server connection';

    public function handle()
    {
        $host = $this->argument('host');
        $port = $this->argument('port');
        $device = $this->option('device');
        $imei = $this->option('imei');
        $interval = $this->option('interval');
        $count = $this->option('count');

        $this->info("🎯 GPS Device Simulator");
        $this->info("📡 Target: $host:$port");
        $this->info("📱 Device: $device");
        $this->info("🆔 IMEI: $imei");
        $this->info("⏱️  Interval: {$interval}s");
        $this->info("🔢 Messages: $count");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $connector = new Connector();
        
        $connector->connect("$host:$port")->then(
            function ($connection) use ($device, $imei, $interval, $count) {
                $this->info("✅ Connected to GPS server!");
                
                // Send login packet first
                $loginPacket = $this->generateLoginPacket($device, $imei);
                $connection->write($loginPacket);
                $this->info("📤 Login packet sent: " . bin2hex($loginPacket));
                
                $messagesSent = 0;
                $lat = 23.0225; // Starting latitude (Ahmedabad)
                $lng = 72.5714; // Starting longitude
                
                // Send location packets
                $timer = \React\EventLoop\Loop::get()->addPeriodicTimer($interval, function () use ($connection, $device, $imei, &$messagesSent, $count, &$lat, &$lng) {
                    if ($messagesSent >= $count) {
                        $this->info("🏁 Simulation completed!");
                        $connection->close();
                        return;
                    }
                    
                    // Simulate movement
                    $lat += (rand(-100, 100) / 10000); // Small random movement
                    $lng += (rand(-100, 100) / 10000);
                    $speed = rand(0, 80); // Random speed 0-80 km/h
                    $direction = rand(0, 360); // Random direction
                    
                    $locationPacket = $this->generateLocationPacket($device, $imei, $lat, $lng, $speed, $direction);
                    $connection->write($locationPacket);
                    
                    $messagesSent++;
                    $this->info("📤 Message $messagesSent/$count sent - Lat: $lat, Lng: $lng, Speed: {$speed}km/h");
                });
                
                $connection->on('data', function ($data) {
                    $this->info("📥 Server response: " . bin2hex($data));
                });
                
                $connection->on('close', function () {
                    $this->info("❌ Connection closed by server");
                });
                
            },
            function ($error) {
                $this->error("💥 Connection failed: " . $error->getMessage());
                $this->error("🔍 Make sure GPS server is running on the target host:port");
            }
        );

        // Keep the simulator running
        \React\EventLoop\Loop::get()->run();
    }

    private function generateLoginPacket($device, $imei)
    {
        switch ($device) {
            case 'GT06N':
                // GT06N login packet structure
                $packet = '7878'; // Start flag
                $packet .= '11'; // Length
                $packet .= '01'; // Protocol number (login)
                $packet .= str_pad(dechex($imei), 16, '0', STR_PAD_LEFT); // IMEI
                $packet .= '0001'; // Info serial number
                $packet .= 'D9DC'; // CRC
                $packet .= '0D0A'; // Stop flag
                return pack('H*', $packet);
                
            case 'TK103':
                return "({$imei}BR00)";
                
            case 'Teltonika':
                // Simplified Teltonika login
                return pack('H*', '000F313233343536373839303132333435');
                
            case 'Queclink':
                return "+ACK:GTHBD,210101,{$imei},,20240101120000,0460\$";
                
            default:
                return "LOGIN:{$imei}";
        }
    }

    private function generateLocationPacket($device, $imei, $lat, $lng, $speed, $direction)
    {
        switch ($device) {
            case 'GT06N':
                // Simplified GT06N location packet
                $packet = '7878'; // Start flag
                $packet .= '22'; // Length
                $packet .= '22'; // Protocol number (location)
                $packet .= sprintf('%08X', $lat * 1800000); // Latitude
                $packet .= sprintf('%08X', $lng * 1800000); // Longitude
                $packet .= sprintf('%02X', $speed); // Speed
                $packet .= sprintf('%04X', $direction); // Direction
                $packet .= '0001'; // Info serial
                $packet .= 'D9DC'; // CRC
                $packet .= '0D0A'; // Stop flag
                return pack('H*', $packet);
                
            case 'TK103':
                $time = date('His');
                $date = date('dmy');
                return "({$imei}BR00{$time}A{$lat}N{$lng}E000.0{$direction}{$date}FFFFFBFF)";
                
            case 'Teltonika':
                // Simplified Teltonika AVL packet
                return pack('H*', '00000000000000360801000001' . dechex(time()) . '000000000F14F650209CCA80006F00D60400040001');
                
            case 'Queclink':
                $timestamp = date('YmdHis');
                return "+RESP:GTFRI,210101,{$imei},,0,0,1,1,4.3,92,70.0,{$lat},{$lng},{$timestamp},0460,0000,18d8,6141,00,{$timestamp},04F0\$";
                
            default:
                return "LOC:{$imei},{$lat},{$lng},{$speed},{$direction}";
        }
    }
}
