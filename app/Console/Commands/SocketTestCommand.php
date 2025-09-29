<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SocketTestCommand extends Command
{
    protected $signature = 'gps:test-connection {host=localhost} {port=5023} {--timeout=10}';
    protected $description = 'Test socket connection to GPS server';

    public function handle()
    {
        $host = $this->argument('host');
        $port = $this->argument('port');
        $timeout = $this->option('timeout');

        $this->info("🔌 Testing Socket Connection");
        $this->info("🎯 Target: $host:$port");
        $this->info("⏱️  Timeout: {$timeout}s");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        // Test 1: Basic connectivity
        $this->info("🧪 Test 1: Basic Socket Connection");
        if ($this->testBasicConnection($host, $port, $timeout)) {
            $this->info("✅ Basic connection: SUCCESS");
        } else {
            $this->error("❌ Basic connection: FAILED");
            return 1;
        }

        // Test 2: Port accessibility
        $this->info("🧪 Test 2: Port Accessibility");
        if ($this->testPortAccessibility($host, $port)) {
            $this->info("✅ Port accessible: SUCCESS");
        } else {
            $this->error("❌ Port accessible: FAILED");
        }

        // Test 3: Data transmission
        $this->info("🧪 Test 3: Data Transmission");
        if ($this->testDataTransmission($host, $port, $timeout)) {
            $this->info("✅ Data transmission: SUCCESS");
        } else {
            $this->error("❌ Data transmission: FAILED");
        }

        // Test 4: Server response
        $this->info("🧪 Test 4: Server Response");
        if ($this->testServerResponse($host, $port, $timeout)) {
            $this->info("✅ Server response: SUCCESS");
        } else {
            $this->warn("⚠️  Server response: NO RESPONSE (may be normal)");
        }

        $this->line("");
        $this->info("🎉 Connection tests completed!");
        $this->info("💡 If all tests pass, your GPS devices should connect successfully");

        return 0;
    }

    private function testBasicConnection($host, $port, $timeout)
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        
        if ($socket) {
            fclose($socket);
            return true;
        }
        
        $this->error("   Error $errno: $errstr");
        return false;
    }

    private function testPortAccessibility($host, $port)
    {
        $connection = @stream_socket_client(
            "tcp://$host:$port",
            $errno,
            $errstr,
            5,
            STREAM_CLIENT_CONNECT
        );

        if ($connection) {
            fclose($connection);
            return true;
        }

        $this->error("   Error $errno: $errstr");
        return false;
    }

    private function testDataTransmission($host, $port, $timeout)
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        
        if (!$socket) {
            return false;
        }

        // Send test data
        $testData = "TEST_MESSAGE_" . time();
        $bytesSent = fwrite($socket, $testData);
        
        fclose($socket);
        
        if ($bytesSent > 0) {
            $this->line("   📤 Sent $bytesSent bytes");
            return true;
        }
        
        return false;
    }

    private function testServerResponse($host, $port, $timeout)
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        
        if (!$socket) {
            return false;
        }

        // Send GPS-like test data
        $testData = "(123456789012BR00)"; // TK103 login format
        fwrite($socket, $testData);
        
        // Try to read response
        stream_set_timeout($socket, 3);
        $response = fread($socket, 1024);
        
        fclose($socket);
        
        if ($response) {
            $this->line("   📥 Response: " . bin2hex($response));
            return true;
        }
        
        return false;
    }
}
