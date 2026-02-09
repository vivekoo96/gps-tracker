<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Events\VehicleLocationUpdated;
use App\Services\ProtocolDetector;
use App\Models\ProtocolLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;

class GpsTcpServer extends Command
{
    protected $signature = 'gps:tcp-server {--host=0.0.0.0} {--port=5010}';

    protected $description = 'Run a high-performance TCP server for GPS data (ReactPHP)';

    // Store connection context: buffer, authentication state, etc.
    // Keyed by output of spl_object_id($connection) or just use SplObjectStorage
    protected array $connections = [];
    
    protected ProtocolDetector $protocolDetector;
    
    public function __construct(ProtocolDetector $protocolDetector)
    {
        parent::__construct();
        $this->protocolDetector = $protocolDetector;
    }

    public function handle(): int
    {
        $host = $this->option('host');
        $port = (int) $this->option('port');

        // Check if ReactPHP is available (user confirmed it is, but good practice)
        if (!class_exists('React\Socket\SocketServer')) {
            $this->error("ReactPHP is not installed. Please run: composer require react/socket");
            return self::FAILURE;
        }

        $this->info("Starting High-Performance GPS Server on {$host}:{$port} (Event-Driven)");

        $socket = new SocketServer("{$host}:{$port}");

        $socket->on('connection', function (ConnectionInterface $connection) {
            $connId = spl_object_hash($connection);
            $this->info("New Connection: {$connection->getRemoteAddress()} ({$connId})");

            // Initialize Context
            $this->connections[$connId] = [
                'buffer' => '',
                'device' => null,
                'ip' => $connection->getRemoteAddress()
            ];

            // Handle Incoming Data
            $connection->on('data', function ($data) use ($connection, $connId) {
                $this->handleData($connection, $connId, $data);
            });

            // Handle Disconnection
            $connection->on('close', function () use ($connId) {
                $ctx = $this->connections[$connId] ?? null;
                if ($ctx && isset($ctx['device_id'])) {
                    $this->info("Device Offline: {$ctx['device_name']} ({$ctx['device_id']})");
                    
                    // Trigger Webhook
                    app(\App\Services\WebhookService::class)->trigger('device.offline', [
                        'device_id' => $ctx['device_id'],
                        'device_name' => $ctx['device_name'],
                        'timestamp' => now()->toIso8601String(),
                    ]);
                } else {
                    $this->info("Connection Closed: {$connId}");
                }
                unset($this->connections[$connId]);
            });

            // Handle Errors
            $connection->on('error', function (\Exception $e) use ($connId) {
                $this->error("Connection Error [{$connId}]: " . $e->getMessage());
            });
        });

        $socket->on('error', function (\Exception $e) {
            $this->error("Socket Server Error: " . $e->getMessage());
        });

        // Loop runs automatically in recent ReactPHP versions when resources are active, 
        // but explicitly calling it ensures command doesn't exit.
        
        /* @phpstan-ignore-next-line */
        if (class_exists('React\EventLoop\Loop')) {
            Loop::run();
        } else {
             // Fallback for older versions
            $loop = \React\EventLoop\Factory::create();
            $loop->run();
        }

        return self::SUCCESS;
    }

    protected function handleData(ConnectionInterface $connection, string $connId, string $data): void
    {
        if (!isset($this->connections[$connId])) return;

        // Log Raw (Debug)
        // Log::channel('daily')->info("RX [{$connId}]", ['hex' => bin2hex($data)]);

        $ctx = &$this->connections[$connId];
        $ctx['buffer'] .= $data;

        // Packet Framing Strategy:
        // GT06 uses 0x78 0x78 [Len] ... so we can detect start.
        // But TCP is a stream. We might get half a packet or 1.5 packets.
        // For this implementation, we'll try to process whatever is in the buffer.
        // If we process a packet, we remove it from buffer.
        
        $this->processBuffer($connection, $ctx);
    }

    protected function processBuffer(ConnectionInterface $connection, array &$ctx): void
    {
        $buffer = $ctx['buffer'];
        $len = strlen($buffer);
        
        if ($len < 5) return; // Need at least some data

        try {
            // Use protocol detector to identify protocol
            $parser = $this->protocolDetector->detect($buffer);
            $protocolName = $parser->getProtocolName();
            
            // For GT06, we need to extract complete packets
            if ($protocolName === 'gt06') {
                $this->processGT06Buffer($connection, $ctx, $parser);
            } elseif ($protocolName === 'teltonika') {
                $this->processTeltonikaBuffer($connection, $ctx, $parser);
            } elseif ($protocolName === 'queclink') {
                $this->processQueclinkBuffer($connection, $ctx, $parser);
            } elseif ($protocolName === 'tk103') {
                $this->processTK103Buffer($connection, $ctx, $parser);
            } else {
                // Text or unknown protocol - process line by line
                $this->processTextBuffer($connection, $ctx, $parser);
            }
        } catch (\Exception $e) {
            Log::error("Protocol processing error: " . $e->getMessage());
            $ctx['buffer'] = ''; // Clear buffer on error
        }
    }

    protected function processTeltonikaBuffer(ConnectionInterface $connection, array &$ctx, $parser): void
    {
        $buffer = $ctx['buffer'];
        $len = strlen($buffer);
        $hex = bin2hex($buffer);

        // Teltonika preamble is 4 bytes of zero
        if (str_starts_with($hex, '00000000')) {
            if ($len < 8) return;
            
            $dataLength = hexdec(substr($hex, 8, 8));
            $totalExpectedBytes = 4 + 4 + $dataLength + 4; // Preamble + Len + Data + CRC

            if ($len >= $totalExpectedBytes) {
                $packet = substr($buffer, 0, $totalExpectedBytes);
                $this->processPacketWithParser($connection, $ctx, $packet, $parser);
                $ctx['buffer'] = substr($buffer, $totalExpectedBytes);
                
                if (strlen($ctx['buffer']) > 0) {
                    $this->processBuffer($connection, $ctx);
                }
            }
            return;
        }

        // Handle case where it's a direct IMEI login (not prefixed with preamble)
        // Teltonika IMEI is usually 15-17 bytes
        if ($len >= 15 && $len <= 20 && !str_contains($buffer, "\n")) {
             $packet = $buffer;
             $this->processPacketWithParser($connection, $ctx, $packet, $parser);
             $ctx['buffer'] = '';
        }
    }

    protected function processQueclinkBuffer(ConnectionInterface $connection, array &$ctx, $parser): void
    {
        $buffer = $ctx['buffer'];
        
        // Queclink messages end with $ or sometimes newline
        $pos = strpos($buffer, '$');
        if ($pos === false) {
            $pos = strpos($buffer, "\n");
        }

        if ($pos !== false) {
            $packet = substr($buffer, 0, $pos + 1);
            $this->processPacketWithParser($connection, $ctx, $packet, $parser);
            $ctx['buffer'] = substr($buffer, $pos + 1);
            
            if (strlen($ctx['buffer']) > 0) {
                $this->processBuffer($connection, $ctx);
            }
        }
    }

    protected function processGT06Buffer(ConnectionInterface $connection, array &$ctx, $parser): void
    {
        $buffer = $ctx['buffer'];
        $len = strlen($buffer);
        $hex = bin2hex($buffer);

        // CHECK PROTOCOL: GT06 (0x78 0x78) OR (0x79 0x79)
        if (str_starts_with($hex, '7878') || str_starts_with($hex, '7979')) {
            $isExtended = str_starts_with($hex, '7979');
            
            if ($isExtended) {
                // 79 79 [2 bytes Len] ... [2 bytes Stop]
                if ($len < 6) return;
                $pkgLen = hexdec(substr($hex, 4, 4)); 
                $totalExpectedBytes = 2 + 2 + $pkgLen + 2;
                $protocolOffset = 8;
            } else {
                // 78 78 [1 byte Len] ... [2 bytes Stop]
                if ($len < 5) return;
                $pkgLen = hexdec(substr($hex, 4, 2));
                $totalExpectedBytes = 2 + 1 + $pkgLen + 2;
                $protocolOffset = 6;
            }

            if ($len >= $totalExpectedBytes) {
                $packet = substr($buffer, 0, $totalExpectedBytes);
                $this->processPacketWithParser($connection, $ctx, $packet, $parser);
                $ctx['buffer'] = substr($buffer, $totalExpectedBytes);
                
                if (strlen($ctx['buffer']) > 0) {
                    $this->processBuffer($connection, $ctx);
                }
            }
            return;
        }
    }

    protected function processTK103Buffer(ConnectionInterface $connection, array &$ctx, $parser): void
    {
        $buffer = $ctx['buffer'];
        
        // TK103 uses line-based protocol with parentheses
        if (($pos = strpos($buffer, ')')) !== false) {
            $packet = substr($buffer, 0, $pos + 1);
            $this->processPacketWithParser($connection, $ctx, $packet, $parser);
            $ctx['buffer'] = substr($buffer, $pos + 1);
            
            if (strlen($ctx['buffer']) > 0) {
                $this->processBuffer($connection, $ctx);
            }
        }
    }

    protected function processTextBuffer(ConnectionInterface $connection, array &$ctx, $parser): void
    {
        $buffer = $ctx['buffer'];
        
        // Process line by line
        if (($pos = strpos($buffer, "\n")) !== false) {
            $line = substr($buffer, 0, $pos);
            $this->processPacketWithParser($connection, $ctx, $line, $parser);
            $ctx['buffer'] = substr($buffer, $pos + 1);
            
            if (strlen($ctx['buffer']) > 0) {
                $this->processBuffer($connection, $ctx);
            }
        }
    }

    protected function processPacketWithParser(ConnectionInterface $connection, array &$ctx, string $packet, $parser): void
    {
        try {
            // Parse the packet
            $parsedData = $parser->parse($packet);
            $protocolName = $parser->getProtocolName();
            
            // Log protocol activity
            $this->logProtocol($ctx['device_id'] ?? null, $protocolName, $packet, $parsedData, true);
            
            // Handle based on packet type
            switch ($parsedData['type']) {
                case 'login':
                    $this->handleLogin($connection, $ctx, $parsedData, $parser);
                    break;
                
                case 'location':
                    $this->handleLocation($connection, $ctx, $parsedData, $parser);
                    break;
                
                case 'heartbeat':
                    $this->handleHeartbeat($connection, $ctx, $parsedData, $parser);
                    break;
                
                case 'alarm':
                    $this->handleAlarm($connection, $ctx, $parsedData, $parser);
                    break;
            }
        } catch (\Exception $e) {
            Log::error("Packet parsing error: " . $e->getMessage());
            $this->logProtocol($ctx['device_id'] ?? null, $parser->getProtocolName(), $packet, [], false, $e->getMessage());
        }
    }

    protected function handleLogin(ConnectionInterface $connection, array &$ctx, array $parsedData, $parser): void
    {
        $imei = $parsedData['imei'];
        
        // Find device by IMEI
        $device = Device::where('imei', $imei)
            ->orWhere('unique_id', $imei)
            ->first();
        
        if ($device) {
            $ctx['device_id'] = $device->id;
            $ctx['device_name'] = $device->name;
            
            // Update device status and protocol
            $device->update([
                'last_seen_at' => now(),
                'status' => 'active',
                'protocol_type' => $parser->getProtocolName(),
            ]);
            
            $this->info("Login [{$parser->getProtocolName()}]: {$device->name} ({$imei})");
            
            // Trigger Webhook
            app(\App\Services\WebhookService::class)->trigger('device.online', [
                'device_id' => $device->id,
                'device_name' => $device->name,
                'imei' => $imei,
                'protocol' => $parser->getProtocolName(),
                'timestamp' => now()->toIso8601String(),
            ], $device->vendor_id);
            
            // Send login response
            $response = $parser->buildLoginResponse($device);
            $connection->write($response);
            
            // Send pending commands
            $this->sendPendingCommands($connection, $device->id);
        } else {
            $this->warn("Unknown Device Login: {$imei}");
        }
    }

    protected function handleLocation(ConnectionInterface $connection, array &$ctx, array $parsedData, $parser): void
    {
        if (!isset($ctx['device_id'])) return;
        
        // Check if there are multiple records (e.g. Teltonika)
        $records = $parsedData['records'] ?? [$parsedData];

        foreach ($records as $record) {
            // Save GPS data
            $this->saveGpsData($ctx['device_id'], $record);
        }
        
        // Send response
        $response = $parser->buildLocationResponse();
        $connection->write($response);
    }

    protected function handleHeartbeat(ConnectionInterface $connection, array &$ctx, array $parsedData, $parser): void
    {
        if (!isset($ctx['device_id'])) return;
        
        // Update last seen
        Device::where('id', $ctx['device_id'])->update(['last_seen_at' => now()]);
        
        // Send response
        $response = $parser->buildHeartbeatResponse();
        $connection->write($response);
    }

    protected function handleAlarm(ConnectionInterface $connection, array &$ctx, array $parsedData, $parser): void
    {
        if (!isset($ctx['device_id'])) return;
        
        $this->info("🚨 ALARM from device ID: {$ctx['device_id']}");
        
        // Save GPS data
        $this->saveGpsData($ctx['device_id'], $parsedData);
        
        // Create SOS alert if applicable
        // ... (existing SOS logic)
        
        // Send response
        $response = $parser->buildLocationResponse();
        $connection->write($response);
    }

    protected function logProtocol(?int $deviceId, string $protocolType, string $rawData, array $parsedData, bool $success, ?string $error = null): void
    {
        try {
            ProtocolLog::create([
                'device_id' => $deviceId,
                'protocol_type' => $protocolType,
                'raw_data' => bin2hex($rawData),
                'parsed_data' => $parsedData,
                'parse_success' => $success,
                'error_message' => $error,
            ]);
        } catch (\Exception $e) {
            // Silently fail logging to not disrupt GPS processing
        }
    }

    protected function saveGpsData(int $deviceId, array $parsedData): void
    {
        if (!isset($parsedData['latitude']) || !isset($parsedData['longitude'])) {
            return;
        }

        $lat = $parsedData['latitude'];
        $lon = $parsedData['longitude'];

        // Validate coordinates
        if ($lat == 0 || $lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return;
        }

        try {
            // Update device location
            Device::where('id', $deviceId)->update([
                'latitude' => $lat,
                'longitude' => $lon,
                'speed' => $parsedData['speed'] ?? null,
                'heading' => $parsedData['course'] ?? null,
                'altitude' => $parsedData['altitude'] ?? null,
                'satellites' => $parsedData['satellites'] ?? null,
                'last_location_update' => now(),
                'last_seen_at' => now(),
            ]);

            // Save GPS data record
            DB::table('gps_data')->insert([
                'device_id' => $deviceId,
                'latitude' => $lat,
                'longitude' => $lon,
                'speed' => $parsedData['speed'] ?? 0,
                'heading' => $parsedData['course'] ?? 0,
                'altitude' => $parsedData['altitude'] ?? 0,
                'satellites' => $parsedData['satellites'] ?? 0,
                'timestamp' => $parsedData['timestamp'] ?? now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Broadcast location update
            $device = Device::find($deviceId);
            if ($device) {
                broadcast(new VehicleLocationUpdated($device))->toOthers();
            }
        } catch (\Exception $e) {
            Log::error("Failed to save GPS data: " . $e->getMessage());
        }
    }

    protected function processGt06Packet(ConnectionInterface $connection, array &$ctx, string $packet, bool $isExtended = false): void
    {
        $hex = bin2hex($packet);
        $protocolOffset = $isExtended ? 8 : 6;
        $protocolId = substr($hex, $protocolOffset, 2);

        switch ($protocolId) {
            case '01': // Login
                // Terminal ID is 8 bytes. Offset 4 bytes in 7878, 5 bytes in 7979
                $idOffset = $isExtended ? 10 : 8;
                $terminalIdHex = substr($hex, $idOffset, 16);
                $imeiCandidate = ltrim($terminalIdHex, '0');
                
                $this->info("DEBUG: Raw ID={$terminalIdHex}, Stripped={$imeiCandidate}");

                // Direct raw SQL query - no Laravel magic
                $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
                $stmt = $pdo->prepare("SELECT id, name FROM devices WHERE imei = ? OR imei = ? OR unique_id = ? OR unique_id = ? LIMIT 1");
                $stmt->execute([$terminalIdHex, $imeiCandidate, $terminalIdHex, $imeiCandidate]);
                $deviceData = $stmt->fetch(\PDO::FETCH_OBJ);
                
                $this->info("DEBUG: Query result = " . ($deviceData ? "Found ID={$deviceData->id}" : "NOT FOUND"));

                if ($deviceData) {
                    $ctx['device_id'] = $deviceData->id;
                    $ctx['device_name'] = $deviceData->name;
                    
                    $pdo->exec("UPDATE devices SET last_seen_at = NOW(), status = 'active' WHERE id = {$deviceData->id}");
                        
                    $this->info("Login [GT06" . ($isExtended ? "-Ex" : "") . "]: {$deviceData->name} ({$terminalIdHex})");

                    // Standard Reply
                    $serial = substr($hex, -8, 4); 
                    $header = $isExtended ? "79790005" : "787805";
                    $resp = $header . "01" . $serial . "D9DC0D0A"; 
                    $connection->write(hex2bin($resp));
                    
                    // ✅ Check for pending commands and send them
                    $this->sendPendingCommands($connection, $deviceData->id);
                } else {
                    $this->warn("Unknown Device Login: $terminalIdHex (tried: $imeiCandidate)");
                }
                break;

            case '16': // 🚨 SOS / Alarm Packet
                if (isset($ctx['device_id'])) {
                    $this->info("🚨 SOS ALERT from device ID: {$ctx['device_id']} ({$ctx['device_name']})");
                    
                    // Parse location data from alarm packet (similar to location packet)
                    $dataStart = $isExtended ? 10 : 8;
                    
                    $lat = 0;
                    $lon = 0;
                    $speed = 0;
                    
                    if (strlen($hex) >= ($dataStart + 30)) {
                        $latHex = substr($hex, $dataStart + 14, 8);
                        $lonHex = substr($hex, $dataStart + 22, 8);
                        $speedHex = substr($hex, $dataStart + 30, 2);
                        
                        $lat = hexdec($latHex) / 1800000.0;
                        $lon = hexdec($lonHex) / 1800000.0;
                        $speed = hexdec($speedHex);
                    }
                    
                    // Create SOS Alert
                    try {
                        $alertId = DB::table('sos_alerts')->insertGetId([
                            'device_id' => $ctx['device_id'],
                            'latitude' => $lat != 0 ? $lat : null,
                            'longitude' => $lon != 0 ? $lon : null,
                            'speed' => $speed,
                            'status' => 'active',
                            'triggered_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        
                        $this->info("✅ SOS Alert created: ID={$alertId}");
                        
                        // Trigger notifications and broadcast
                        $alert = \App\Models\SosAlert::find($alertId);
                        if ($alert) {
                            // Broadcast real-time alert
                            broadcast(new \App\Events\SosAlertTriggered($alert))->toOthers();
                            
                            // Send notifications (async via queue would be better)
                            $notificationService = app(\App\Services\SosNotificationService::class);
                            $notificationService->sendNotifications($alert);
                        }
                    } catch (\Exception $e) {
                        $this->error("Failed to create SOS alert: " . $e->getMessage());
                    }
                    
                    // Send acknowledgment
                    $serial = substr($hex, -8, 4);
                    $resp = ($isExtended ? "79790005" : "787805") . "16" . $serial . "D9DC0D0A";
                    $connection->write(hex2bin($resp));
                }
                break;

            case '94': // LBS/Status Info (not GPS)
                $deviceName = $ctx['device_name'] ?? 'Unknown';
                $this->info("LBS [{$deviceName}]: Received Cell Tower data (ignored for now)");
                break;

            case '12': // Location
            case '22':
                $this->info("DEBUG: Processing GPS packet hex=" . substr($hex, 0, 100));
                if (isset($ctx['device_id'])) {
                    // Generic GT06 Location Parsing (simplified offsets)
                    $dataStart = $isExtended ? 10 : 8;
                    
                    if (strlen($hex) < ($dataStart + 30)) return;

                    $latHex = substr($hex, $dataStart + 14, 8); 
                    $lonHex = substr($hex, $dataStart + 22, 8); 
                    $speedHex = substr($hex, $dataStart + 30, 2); 

                    $this->info("DEBUG: dataStart={$dataStart} LatHex={$latHex} LonHex={$lonHex}");

                    $lat = hexdec($latHex) / 1800000.0;
                    $lon = hexdec($lonHex) / 1800000.0;
                    $speed = hexdec($speedHex);

                    $this->info("GPS [{$ctx['device_name']}]: lat={$lat}, lng={$lon}, speed={$speed}");

                    if ($lat != 0 && $lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180) {
                        $payload = [
                            'device_id' => $ctx['device_id'],
                            'name' => $ctx['device_name'] ?? 'VH001',
                            'latitude' => $lat,
                            'longitude' => $lon,
                            'speed' => $speed,
                            'fix_time' => now()->toDateTimeString(),
                            'protocol' => $isExtended ? 'GT06-EX' : 'GT06',
                            'last_update' => now()->toDateTimeString(),
                            'status' => 'online'
                        ];

                        // 1. Cache Latest State in Redis (Fast)
                        try {
                            Redis::hset('device:latest', $ctx['device_id'], json_encode($payload));
                            Redis::publish('tracking', json_encode(['event' => 'location.updated', 'data' => $payload]));
                        } catch (\Exception $e) {
                            $this->error("Redis Error: " . $e->getMessage());
                        }

                        // 2. Broadcast via WebSockets (Real-time)
                        broadcast(new VehicleLocationUpdated($ctx['device_id'], $payload))->toOthers();

                        // 3. Persist to DB (History - Background)
                        DB::table('positions')->insert([
                            'device_id' => $ctx['device_id'],
                            'latitude' => $lat,
                            'longitude' => $lon,
                            'speed' => $speed,
                            'fix_time' => now(),
                            'protocol' => $isExtended ? 'GT06-EX' : 'GT06',
                            'raw' => $hex,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        
                        DB::table('devices')
                            ->where('id', $ctx['device_id'])
                            ->update([
                                'last_seen_at' => now(),
                                'status' => 'active',
                                'last_location_update' => now(),
                                'latitude' => $lat,
                                'longitude' => $lon,
                                'speed' => $speed,
                                'heading' => 0,
                                'updated_at' => now()
                            ]);
                        
                        // 🚨 DRIVER BEHAVIOR: Detect violations in real-time
                        try {
                            $violationService = app(\App\Services\ViolationDetectionService::class);
                            
                            // Get current GPS data
                            $currentGps = DB::table('positions')
                                ->where('device_id', $ctx['device_id'])
                                ->latest('id')
                                ->first();
                            
                            // Get previous GPS data
                            $previousGps = DB::table('positions')
                                ->where('device_id', $ctx['device_id'])
                                ->where('id', '<', $currentGps->id)
                                ->latest('id')
                                ->first();
                            
                            if ($currentGps && $previousGps) {
                                // Convert to GpsData objects (simplified)
                                $current = (object)[
                                    'device_id' => $ctx['device_id'],
                                    'latitude' => $currentGps->latitude,
                                    'longitude' => $currentGps->longitude,
                                    'speed' => $currentGps->speed,
                                    'course' => 0,
                                    'ignition' => true,
                                    'timestamp' => \Carbon\Carbon::parse($currentGps->fix_time),
                                ];
                                
                                $previous = (object)[
                                    'device_id' => $previousGps->device_id,
                                    'latitude' => $previousGps->latitude,
                                    'longitude' => $previousGps->longitude,
                                    'speed' => $previousGps->speed,
                                    'course' => 0,
                                    'ignition' => true,
                                    'timestamp' => \Carbon\Carbon::parse($previousGps->fix_time),
                                ];
                                
                                // Detect violations
                                $violations = $violationService->detectViolations($current, $previous);
                                
                                if (!empty($violations)) {
                                    $this->info("🚨 Detected " . count($violations) . " violation(s) for device {$ctx['device_id']}");
                                }
                            }
                        } catch (\Exception $e) {
                            $this->error("Violation detection error: " . $e->getMessage());
                        }
                    }
                }
                break;

            case '13': // Heartbeat
                if (isset($ctx['device_id'])) {
                    \Illuminate\Support\Facades\DB::table('devices')
                        ->where('id', $ctx['device_id'])
                        ->update(['last_seen_at' => now()]);
                    
                    $serial = substr($hex, -8, 4);
                    $resp = ($isExtended ? "79790005" : "787805") . "13" . $serial . "D9DC0D0A";
                    $connection->write(hex2bin($resp));
                }
                break;
            
            case '22': // Alarm Data (often contains location too)
                // Similar to 12 but different offsets. For now treat as Heartbeat to keep alive.
                if ($ctx['device']) {
                     $ctx['device']->update(['last_seen_at' => now()]);
                     // Acknowledge
                     $serial = substr($hex, -8, 4);
                     $resp = "78780522" . $serial . "D9DC0D0A";
                     $connection->write(hex2bin($resp));
                }
                break;
        }
    }

    protected function processTextPacket(ConnectionInterface $connection, array &$ctx, string $line): void
    {
        // Simple Text Protocol Handler
        // e.g. "ID=12345,LAT=...,LON=..."
        // Or "IMEI=12345"
        
        if (preg_match('/IMEI[:=](\d+)/i', $line, $matches)) {
            $imei = $matches[1];
            $device = Device::where('imei', $imei)->orWhere('unique_id', $imei)->first();
            if ($device) {
                $ctx['device'] = $device;
                $device->update(['last_seen_at' => now(), 'status' => 'active']);
                $connection->write("OK\n");
                $this->info("Login [Text]: {$device->name}");
            }
        } elseif ($ctx['device']) {
            $ctx['device']->update(['last_seen_at' => now()]);
        }
    }

    /**
     * Send pending commands to device
     */
    protected function sendPendingCommands(ConnectionInterface $connection, int $deviceId): void
    {
        try {
            // Fetch pending commands
            $commands = DB::table('device_commands')
                ->where('device_id', $deviceId)
                ->where('status', 'pending')
                ->orderBy('created_at', 'asc')
                ->get();

            if ($commands->isEmpty()) {
                return;
            }

            $this->info("Found " . $commands->count() . " pending command(s) for device ID: {$deviceId}");

            foreach ($commands as $command) {
                // Send command hex
                $commandBinary = hex2bin($command->command_hex);
                $connection->write($commandBinary);
                
                $this->info("Sent {$command->command_type} command to device ID: {$deviceId}");
                
                // Update command status
                DB::table('device_commands')
                    ->where('id', $command->id)
                    ->update([
                        'status' => 'sent',
                        'sent_at' => now()
                    ]);
            }
        } catch (\Exception $e) {
            $this->error("Error sending commands: " . $e->getMessage());
        }
    }
}


