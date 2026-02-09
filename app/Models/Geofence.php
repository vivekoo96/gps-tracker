<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Geofence extends Model
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

    protected $fillable = [
        'name',
        'description',
        'latitude',
        'longitude',
        'radius',
        'color',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this geofence
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all events for this geofence
     */
    public function events()
    {
        return $this->hasMany(GeofenceEvent::class);
    }

    /**
     * Get the alert configuration for this geofence
     */
    public function alert()
    {
        return $this->hasOne(GeofenceAlert::class);
    }

    /**
     * Check if a point (lat, lng) is inside this geofence
     *
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @return bool
     */
    public function containsPoint($lat, $lng)
    {
        $distance = $this->calculateDistance($lat, $lng);
        return $distance <= $this->radius;
    }

    /**
     * Calculate distance from geofence center to a point using Haversine formula
     *
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @return float Distance in meters
     */
    private function calculateDistance($lat, $lng)
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get recent events for this geofence
     */
    public function recentEvents($limit = 10)
    {
        return $this->events()
            ->with('device')
            ->orderBy('event_time', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get event count for today
     */
    public function todayEventsCount()
    {
        return $this->events()
            ->whereDate('event_time', today())
            ->count();
    }

    /**
     * Scope to get only active geofences
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
