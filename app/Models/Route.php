<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\VendorScope);
        
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->vendor_id) {
                $model->vendor_id = auth()->user()->vendor_id;
            }
        });
    }

    protected $guarded = [];

    protected $casts = [
        'stops' => 'array',
    ];
}
