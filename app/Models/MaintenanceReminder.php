<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceReminder extends Model
{
    protected $fillable = [
        'vendor_id', 'device_id', 'schedule_id', 'reminder_type',
        'task_name', 'due_km', 'due_date', 'current_km', 'km_remaining',
        'days_remaining', 'message', 'is_sent', 'is_acknowledged',
        'sent_at', 'acknowledged_at',
    ];

    protected $casts = [
        'due_km' => 'decimal:2',
        'current_km' => 'decimal:2',
        'km_remaining' => 'decimal:2',
        'due_date' => 'date',
        'is_sent' => 'boolean',
        'is_acknowledged' => 'boolean',
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MaintenanceSchedule::class);
    }

    public function scopeUnacknowledged($query)
    {
        return $query->where('is_acknowledged', false);
    }

    public function acknowledge()
    {
        $this->update([
            'is_acknowledged' => true,
            'acknowledged_at' => now(),
        ]);
    }
}
