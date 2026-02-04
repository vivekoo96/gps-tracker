<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dashcam extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'camera_model',
        'resolution',
        'storage_capacity',
        'stream_url',
        'status',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
