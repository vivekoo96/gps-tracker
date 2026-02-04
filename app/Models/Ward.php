<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    use HasFactory;

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
