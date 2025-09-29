<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GpsData extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'latitude',
        'longitude',
        'speed',
        'direction',
        'altitude',
        'satellites',
        'battery_level',
        'signal_strength',
        'recorded_at',
        'raw_data'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'speed' => 'decimal:2',
        'direction' => 'decimal:2',
        'altitude' => 'decimal:2',
        'battery_level' => 'integer',
        'signal_strength' => 'integer',
        'recorded_at' => 'datetime'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    // Get latest position for a device
    public static function getLatestPosition($deviceId)
    {
        return static::where('device_id', $deviceId)
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->orderBy('recorded_at', 'desc')
                    ->first();
    }

    // Get device track/history
    public static function getDeviceTrack($deviceId, $from = null, $to = null)
    {
        $query = static::where('device_id', $deviceId)
                      ->whereNotNull('latitude')
                      ->whereNotNull('longitude');

        if ($from) {
            $query->where('recorded_at', '>=', $from);
        }

        if ($to) {
            $query->where('recorded_at', '<=', $to);
        }

        return $query->orderBy('recorded_at', 'asc')->get();
    }
}
