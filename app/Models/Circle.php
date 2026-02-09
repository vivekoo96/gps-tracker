<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Circle extends Model
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

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function wards()
    {
        return $this->hasMany(Ward::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
