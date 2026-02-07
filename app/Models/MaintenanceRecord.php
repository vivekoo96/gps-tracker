<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    protected $fillable = [
        'vendor_id', 'device_id', 'schedule_id', 'task_name', 'description',
        'category', 'service_type', 'odometer_reading', 'service_date',
        'next_service_km', 'next_service_date', 'cost', 'labor_cost',
        'parts_cost', 'service_provider', 'technician_name',
        'invoice_number', 'invoice_image', 'notes', 'performed_by',
    ];

    protected $casts = [
        'odometer_reading' => 'decimal:2',
        'next_service_km' => 'decimal:2',
        'cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'service_date' => 'date',
        'next_service_date' => 'date',
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

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
