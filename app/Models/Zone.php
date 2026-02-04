<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function circles()
    {
        return $this->hasMany(Circle::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
