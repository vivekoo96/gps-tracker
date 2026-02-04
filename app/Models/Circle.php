<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Circle extends Model
{
    use HasFactory;

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
