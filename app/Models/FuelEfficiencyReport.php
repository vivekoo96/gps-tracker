<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelEfficiencyReport extends Model
{
    protected $fillable = [
        'vendor_id', 'device_id', 'vehicle_id', 'period',
        'period_start', 'period_end', 'total_fuel_consumed',
        'total_distance', 'average_efficiency', 'total_refuel_amount',
        'total_refuel_cost', 'total_theft_amount', 'idle_fuel_consumed',
        'trips_count', 'calculated_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_fuel_consumed' => 'decimal:2',
        'total_distance' => 'decimal:2',
        'average_efficiency' => 'decimal:2',
        'total_refuel_amount' => 'decimal:2',
        'total_refuel_cost' => 'decimal:2',
        'total_theft_amount' => 'decimal:2',
        'idle_fuel_consumed' => 'decimal:2',
        'calculated_at' => 'datetime',
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
    public function scopeForPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeTopPerformers($query, $limit = 10)
    {
        return $query->orderBy('average_efficiency', 'desc')->limit($limit);
    }

    // Accessors
    public function getEfficiencyLabelAttribute()
    {
        return number_format($this->average_efficiency, 2) . ' km/L';
    }

    public function getCostPerKmAttribute()
    {
        if ($this->total_distance > 0) {
            return $this->total_refuel_cost / $this->total_distance;
        }
        return 0;
    }
}
