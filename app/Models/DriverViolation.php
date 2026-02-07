<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverViolation extends Model
{
    protected $fillable = [
        'vendor_id',
        'device_id',
        'driver_id',
        'violation_type',
        'severity',
        'latitude',
        'longitude',
        'address',
        'speed',
        'speed_limit',
        'metadata',
        'occurred_at',
        'acknowledged_at',
        'acknowledged_by',
        'notes',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'speed' => 'decimal:2',
        'speed_limit' => 'decimal:2',
    ];

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    // Scopes
    public function scopeUnacknowledged($query)
    {
        return $query->whereNull('acknowledged_at');
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('violation_type', $type);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('occurred_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getSeverityLabelAttribute()
    {
        return ucfirst($this->severity);
    }

    public function getTypeLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->violation_type));
    }

    public function getLocationUrlAttribute()
    {
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    // Methods
    public function acknowledge($userId, $notes = null)
    {
        $this->update([
            'acknowledged_at' => now(),
            'acknowledged_by' => $userId,
            'notes' => $notes,
        ]);
    }

    public function getSeverityColor()
    {
        return match($this->severity) {
            'low' => 'yellow',
            'medium' => 'orange',
            'high' => 'red',
            'critical' => 'purple',
            default => 'gray',
        };
    }

    public function getIcon()
    {
        return match($this->violation_type) {
            'harsh_braking' => '🛑',
            'harsh_acceleration' => '⚡',
            'harsh_cornering' => '↩️',
            'speeding' => '🚨',
            'excessive_idling' => '⏱️',
            'seatbelt' => '🔒',
            'phone_usage' => '📱',
            default => '⚠️',
        };
    }
}
