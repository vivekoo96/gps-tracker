<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeploymentCheckCommand extends Command
{
    protected $signature = 'gps:check-deployment';
    protected $description = 'Check if GPS server is ready for deployment';

    public function handle()
    {
        $this->info("🚀 GPS Server Deployment Readiness Check");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $checks = [
            'PHP Extensions' => $this->checkPhpExtensions(),
            'Database Connection' => $this->checkDatabase(),
            'Required Directories' => $this->checkDirectories(),
            'Environment Configuration' => $this->checkEnvironment(),
            'ReactPHP Dependencies' => $this->checkReactPHP(),
            'Port Availability' => $this->checkPorts(),
            'File Permissions' => $this->checkPermissions(),
        ];

        $passed = 0;
        $total = count($checks);

        foreach ($checks as $checkName => $result) {
            if ($result['status']) {
                $this->info("✅ $checkName: " . $result['message']);
                $passed++;
            } else {
                $this->error("❌ $checkName: " . $result['message']);
            }
        }

        $this->line("");
        $this->info("📊 Results: $passed/$total checks passed");

        if ($passed === $total) {
            $this->info("🎉 Your server is ready for GPS deployment!");
            $this->displayDeploymentInstructions();
        } else {
            $this->error("⚠️  Please fix the failed checks before deployment");
            $this->displayFixInstructions($checks);
        }

        return $passed === $total ? 0 : 1;
    }

    private function checkPhpExtensions()
    {
        $required = ['sockets', 'pcntl', 'posix'];
        $missing = [];

        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }

        if (empty($missing)) {
            return ['status' => true, 'message' => 'All required extensions loaded'];
        }

        return ['status' => false, 'message' => 'Missing extensions: ' . implode(', ', $missing)];
    }

    private function checkDatabase()
    {
        try {
            \DB::connection()->getPdo();
            
            // Check if GPS tables exist
            $tables = ['devices', 'gps_data'];
            $missing = [];
            
            foreach ($tables as $table) {
                if (!\Schema::hasTable($table)) {
                    $missing[] = $table;
                }
            }
            
            if (empty($missing)) {
                return ['status' => true, 'message' => 'Database connected and tables exist'];
            }
            
            return ['status' => false, 'message' => 'Missing tables: ' . implode(', ', $missing)];
            
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    private function checkDirectories()
    {
        $dirs = [
            storage_path('logs'),
            storage_path('app'),
            storage_path('framework/cache'),
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir) || !is_writable($dir)) {
                return ['status' => false, 'message' => "Directory not writable: $dir"];
            }
        }

        return ['status' => true, 'message' => 'All directories accessible'];
    }

    private function checkEnvironment()
    {
        $required = ['DB_CONNECTION', 'DB_HOST', 'DB_DATABASE'];
        $missing = [];

        foreach ($required as $var) {
            if (!env($var)) {
                $missing[] = $var;
            }
        }

        if (empty($missing)) {
            return ['status' => true, 'message' => 'Environment configured'];
        }

        return ['status' => false, 'message' => 'Missing env vars: ' . implode(', ', $missing)];
    }

    private function checkReactPHP()
    {
        try {
            if (class_exists('React\Socket\SocketServer')) {
                return ['status' => true, 'message' => 'ReactPHP installed'];
            }
        } catch (\Exception $e) {
            // Class not found
        }

        return ['status' => false, 'message' => 'ReactPHP not installed. Run: composer require react/socket'];
    }

    private function checkPorts()
    {
        $ports = [5023, 8082, 5027, 6001];
        $unavailable = [];

        foreach ($ports as $port) {
            $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
            if ($socket) {
                fclose($socket);
                $unavailable[] = $port;
            }
        }

        if (empty($unavailable)) {
            return ['status' => true, 'message' => 'All GPS ports available'];
        }

        return ['status' => false, 'message' => 'Ports in use: ' . implode(', ', $unavailable)];
    }

    private function checkPermissions()
    {
        $files = [
            base_path('artisan'),
            storage_path('logs/laravel.log'),
        ];

        foreach ($files as $file) {
            if (file_exists($file) && !is_writable($file)) {
                return ['status' => false, 'message' => "File not writable: $file"];
            }
        }

        return ['status' => true, 'message' => 'File permissions OK'];
    }

    private function displayDeploymentInstructions()
    {
        $this->line("");
        $this->info("📋 Deployment Instructions:");
        $this->line("1. Upload your code to the server");
        $this->line("2. Run: composer install --no-dev");
        $this->line("3. Run: php artisan migrate");
        $this->line("4. Start GPS servers:");
        $this->line("   php artisan gps:test-server 5023 --debug &");
        $this->line("   php artisan gps:test-server 8082 --debug &");
        $this->line("   php artisan gps:test-server 5027 --debug &");
        $this->line("   php artisan gps:test-server 6001 --debug &");
        $this->line("5. Test connection: php artisan gps:test-connection your-domain.com 5023");
        $this->line("6. Configure your GPS devices with your server address");
    }

    private function displayFixInstructions($checks)
    {
        $this->line("");
        $this->error("🔧 Fix Instructions:");
        
        foreach ($checks as $checkName => $result) {
            if (!$result['status']) {
                $this->line("• $checkName: " . $result['message']);
            }
        }
    }
}
