<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelAlert extends Model
{
    protected $fillable = [
        'vendor_id', 'device_id', 'alert_type', 'severity',
        'title', 'message', 'fuel_level', 'metadata',
        'is_read', 'sent_at', 'read_at',
    ];

    protected $casts = [
        'fuel_level' => 'decimal:2',
        'metadata' => 'array',
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
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

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('sent_at', '>=', now()->subDays($days));
    }

    // Methods
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
