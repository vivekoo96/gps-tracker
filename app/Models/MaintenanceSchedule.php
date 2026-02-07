<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceSchedule extends Model
{
    protected $fillable = [
        'vendor_id', 'device_id', 'vehicle_type', 'task_name', 'description',
        'category', 'interval_type', 'interval_km', 'interval_days',
        'estimated_cost', 'estimated_duration', 'reminder_km_before',
        'reminder_days_before', 'is_active',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, 'schedule_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
