<?php

namespace App\Events;

use App\Models\SosAlert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SosAlertTriggered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $alert;

    public function __construct(SosAlert $alert)
    {
        $this->alert = $alert->load('device');
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('sos-alerts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'sos.triggered';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->alert->id,
            'device_id' => $this->alert->device_id,
            'device_name' => $this->alert->device->name ?? 'Unknown',
            'latitude' => $this->alert->latitude,
            'longitude' => $this->alert->longitude,
            'speed' => $this->alert->speed,
            'triggered_at' => $this->alert->triggered_at->toISOString(),
            'status' => $this->alert->status
        ];
    }
}
