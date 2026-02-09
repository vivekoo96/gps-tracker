<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelSensor extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\VendorScope);
        
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->vendor_id && !isset($model->vendor_id)) {
                $model->vendor_id = auth()->user()->vendor_id;
            }
        });
    }

    protected $fillable = [
        'device_id',
        'tank_capacity',
        'current_level',
        'calibration_data',
        'data_source',
        'status',
    ];

    protected $casts = [
        'tank_capacity' => 'decimal:2',
        'current_level' => 'decimal:2',
        'calibration_data' => 'array',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
