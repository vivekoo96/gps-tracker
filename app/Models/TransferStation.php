<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferStation extends Model
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

    protected $guarded = [];

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function getWastePercentageAttribute()
    {
        if ($this->capacity <= 0) return 0;
        return round(($this->current_load / $this->capacity) * 100, 1);
    }
}
