<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
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

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public function transferStations()
    {
        return $this->hasMany(TransferStation::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
