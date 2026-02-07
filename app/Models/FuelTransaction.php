<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelTransaction extends Model
{
    protected $fillable = [
        'vendor_id', 'device_id', 'vehicle_id', 'transaction_type',
        'fuel_before', 'fuel_after', 'fuel_change', 'odometer',
        'latitude', 'longitude', 'cost', 'price_per_liter',
        'station_name', 'receipt_image', 'notes',
        'detected_at', 'confirmed_at', 'confirmed_by',
    ];

    protected $casts = [
        'fuel_before' => 'decimal:2',
        'fuel_after' => 'decimal:2',
        'fuel_change' => 'decimal:2',
        'odometer' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'cost' => 'decimal:2',
        'price_per_liter' => 'decimal:2',
        'detected_at' => 'datetime',
        'confirmed_at' => 'datetime',
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

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeUnconfirmed($query)
    {
        return $query->whereNull('confirmed_at');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('detected_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getTypeLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->transaction_type));
    }

    public function getTypeBadgeColorAttribute()
    {
        return match($this->transaction_type) {
            'refuel' => 'green',
            'consumption' => 'blue',
            'theft' => 'red',
            'adjustment' => 'yellow',
            default => 'gray',
        };
    }

    public function getLocationUrlAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }
        return null;
    }

    // Methods
    public function confirm($userId, $notes = null)
    {
        $this->update([
            'confirmed_at' => now(),
            'confirmed_by' => $userId,
            'notes' => $notes ?? $this->notes,
        ]);
    }

    public function isRefueling()
    {
        return $this->transaction_type === 'refuel';
    }

    public function isTheft()
    {
        return $this->transaction_type === 'theft';
    }
}
