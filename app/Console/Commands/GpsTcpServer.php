<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\Position;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GpsTcpServer extends Command
{
    protected $signature = 'gps:tcp-server {--host=0.0.0.0} {--port=5010}';

    protected $description = 'Run a simple TCP server to ingest GPS data (GT800/MT100)';

    public function handle(): int
    {
        $host = $this->option('host');
        $port = (int) $this->option('port');

        $server = @stream_socket_server("tcp://{$host}:{$port}", $errno, $errstr);
        if (! $server) {
            $this->error("Failed to bind {$host}:{$port} - {$errstr} ({$errno})");
            return self::FAILURE;
        }

        stream_set_blocking($server, false);
        $this->info("GPS TCP server listening on {$host}:{$port}");

        $clients = [];

        while (true) {
            $client = @stream_socket_accept($server, 0);
            if ($client) {
                stream_set_blocking($client, false);
                $clients[(int)$client] = [
                    'stream' => $client,
                    'buffer' => '',
                    'device' => null,
                ];
            }

            foreach ($clients as $key => $ctx) {
                $stream = $ctx['stream'];
                $data = @fread($stream, 8192);
                if ($data === '' || $data === false) {
                    continue;
                }

                $clients[$key]['buffer'] .= $data;

                // Handle line-delimited messages (\r\n) or full buffer fallback
                $messages = preg_split("/\r?\n/", $clients[$key]['buffer']);
                // keep last partial line in buffer
                $clients[$key]['buffer'] = array_pop($messages);

                foreach ($messages as $message) {
                    $message = trim($message);
                    if ($message === '') continue;
                    $this->processMessage($clients, $key, $message);
                }
            }

            // Clean up closed clients
            foreach ($clients as $key => $ctx) {
                if (feof($ctx['stream'])) {
                    fclose($ctx['stream']);
                    unset($clients[$key]);
                }
            }

            usleep(50000); // 50ms
        }

        return self::SUCCESS;
    }

    protected function processMessage(array &$clients, int $key, string $message): void
    {
        $ctx = &$clients[$key];
        $device = $ctx['device'];

        // Very basic protocol handling:
        // Expect first message to be IMEI line: IMEI:123456789012345
        if (! $device) {
            if (preg_match('/IMEI\s*[:=]\s*(\d{10,20})/i', $message, $m)) {
                $imei = $m[1];
                $device = Device::where('imei', $imei)->first();
                if (! $device) {
                    $this->warn("Unknown IMEI {$imei}");
                    fwrite($ctx['stream'], "ERR Unknown IMEI\r\n");
                    return;
                }
                $ctx['device'] = $device;
                $device->update(['last_seen_at' => now(), 'status' => 'active']);
                fwrite($ctx['stream'], "OK\r\n");
                return;
            }

            // Some devices send IMEI as a standalone numeric line
            if (preg_match('/^(\d{10,20})$/', $message, $m)) {
                $imei = $m[1];
                $device = Device::where('imei', $imei)->first();
                if (! $device) {
                    $this->warn("Unknown IMEI {$imei}");
                    fwrite($ctx['stream'], "ERR Unknown IMEI\r\n");
                    return;
                }
                $ctx['device'] = $device;
                $device->update(['last_seen_at' => now(), 'status' => 'active']);
                fwrite($ctx['stream'], "OK\r\n");
                return;
            }
        }

        // After identification: expect CSV like: time,lat,lon,speed,course,ignition
        // Example: 2025-09-24T12:00:00Z,23.034560,72.512340,45.2,180,1
        if ($device) {
            try {
                $parts = array_map('trim', explode(',', $message));
                if (count($parts) >= 3) {
                    $fixTime = \Carbon\Carbon::parse($parts[0]);
                    $lat = (float)$parts[1];
                    $lon = (float)$parts[2];
                    $speed = isset($parts[3]) ? (float)$parts[3] : null;
                    $course = isset($parts[4]) ? (float)$parts[4] : null;
                    $ignition = isset($parts[5]) ? (bool)intval($parts[5]) : null;

                    Position::create([
                        'device_id' => $device->id,
                        'fix_time' => $fixTime,
                        'latitude' => $lat,
                        'longitude' => $lon,
                        'speed' => $speed,
                        'course' => $course,
                        'ignition' => $ignition,
                        'raw' => $message,
                    ]);
                    $device->update(['last_seen_at' => now()]);
                    fwrite($ctx['stream'], "ACK\r\n");
                    return;
                }
            } catch (\Throwable $e) {
                Log::warning('GPS parse error', ['error' => $e->getMessage(), 'message' => $message]);
            }
        }

        fwrite($ctx['stream'], "ERR\r\n");
    }
}


