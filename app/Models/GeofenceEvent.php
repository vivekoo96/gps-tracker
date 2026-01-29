<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeofenceEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'geofence_id',
        'device_id',
        'event_type',
        'latitude',
        'longitude',
        'event_time',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'event_time' => 'datetime',
    ];

    /**
     * Get the geofence this event belongs to
     */
    public function geofence()
    {
        return $this->belongsTo(Geofence::class);
    }

    /**
     * Get the device that triggered this event
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Scope to get only entry events
     */
    public function scopeEntries($query)
    {
        return $query->where('event_type', 'enter');
    }

    /**
     * Scope to get only exit events
     */
    public function scopeExits($query)
    {
        return $query->where('event_type', 'exit');
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_time', [$startDate, $endDate]);
    }

    /**
     * Get formatted event type
     */
    public function getFormattedEventTypeAttribute()
    {
        return ucfirst($this->event_type);
    }
}
