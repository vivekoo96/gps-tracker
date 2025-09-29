<?php

namespace App\Events;

use App\Models\Device;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GpsDataReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $device;
    public $gpsData;

    public function __construct(Device $device, array $gpsData)
    {
        $this->device = $device;
        $this->gpsData = $gpsData;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('gps-tracking'),
            new Channel('device-' . $this->device->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'gps.data.received';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'device_id' => $this->device->id,
            'device_name' => $this->device->name,
            'latitude' => $this->gpsData['latitude'],
            'longitude' => $this->gpsData['longitude'],
            'speed' => $this->gpsData['speed'] ?? 0,
            'heading' => $this->gpsData['heading'] ?? null,
            'battery_level' => $this->gpsData['battery_level'] ?? null,
            'is_moving' => ($this->gpsData['speed'] ?? 0) > 1,
            'timestamp' => now()->toISOString(),
        ];
    }
}
