<?php

namespace App\Notifications;

use App\Models\Device;
use App\Models\Geofence;
use App\Models\GeofenceEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GeofenceNotification extends Notification
{
    use Queueable;

    protected $geofence;
    protected $device;
    protected $eventType;
    protected $event;

    /**
     * Create a new notification instance.
     */
    public function __construct(Geofence $geofence, Device $device, string $eventType, GeofenceEvent $event)
    {
        $this->geofence = $geofence;
        $this->device = $device;
        $this->eventType = $eventType;
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $action = $this->eventType === 'enter' ? 'entered' : 'exited';
        
        return (new MailMessage)
                    ->subject("Geofence Alert: {$this->device->name} {$action} {$this->geofence->name}")
                    ->line("Device {$this->device->name} has {$action} the geofence '{$this->geofence->name}'.")
                    ->line("Event Time: {$this->event->event_time->format('M d, Y H:i:s')}")
                    ->line("Location: {$this->event->latitude}, {$this->event->longitude}")
                    ->action('View Details', url("/admin/geofences/{$this->geofence->id}/events"))
                    ->line('Thank you for using our GPS tracking system!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $action = $this->eventType === 'enter' ? 'entered' : 'exited';
        
        return [
            'message' => "{$this->device->name} {$action} {$this->geofence->name}",
            'geofence_id' => $this->geofence->id,
            'geofence_name' => $this->geofence->name,
            'device_id' => $this->device->id,
            'device_name' => $this->device->name,
            'event_type' => $this->eventType,
            'event_time' => $this->event->event_time,
            'latitude' => $this->event->latitude,
            'longitude' => $this->event->longitude,
        ];
    }
}
