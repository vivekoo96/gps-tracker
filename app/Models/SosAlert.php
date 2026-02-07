<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SosAlert extends Model
{
    protected $fillable = [
        'device_id',
        'latitude',
        'longitude',
        'speed',
        'location_address',
        'status',
        'triggered_at',
        'acknowledged_at',
        'acknowledged_by',
        'notes'
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * Check if alert is still active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Acknowledge the SOS alert
     */
    public function acknowledge(int $userId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => $userId,
            'notes' => $notes
        ]);
    }

    /**
     * Resolve the SOS alert
     */
    public function resolve(int $userId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'acknowledged_at' => $this->acknowledged_at ?? now(),
            'acknowledged_by' => $userId,
            'notes' => $notes
        ]);
    }

    /**
     * Mark as false alarm
     */
    public function markAsFalseAlarm(int $userId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'false_alarm',
            'acknowledged_at' => now(),
            'acknowledged_by' => $userId,
            'notes' => $notes
        ]);
    }
}
