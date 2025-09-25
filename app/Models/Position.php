<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id','fix_time','latitude','longitude','speed','course','altitude','satellites','ignition','attributes','raw',
    ];

    protected $casts = [
        'fix_time' => 'datetime',
        'attributes' => 'array',
        'ignition' => 'boolean',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}


