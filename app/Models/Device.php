<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\VendorScope);
        
        static::creating(function ($device) {
            if (empty($device->api_secret)) {
                $device->api_secret = \Illuminate\Support\Str::random(32);
            }

            if (auth()->check() && !auth()->user()->isSuperAdmin() && auth()->user()->vendor_id) {
                $device->vendor_id = auth()->user()->vendor_id;
            }
        });
    }

    protected $fillable = [
        // General fields
        'name', 'unit_type', 'device_type', 'vendor_id', 'server_address', 'unique_id', 
        'phone_number', 'password', 'creator', 'account',
        
        // GPS tracking fields
        'latitude', 'longitude', 'speed', 'battery_level', 'last_location_update',
        'location_address', 'is_moving', 'heading', 'altitude', 'satellites',
        
        // Sensor fields
        'mileage_counter', 'mileage_current_value', 'mileage_auto',
        'engine_hours_counter', 'engine_hours_current_value', 'engine_hours_auto',
        'gprs_traffic_counter', 'gprs_traffic_auto',
        
        // Legacy fields
        'model', 'imei', 'sim_number', 'status', 'last_seen_at', 'meta',

        // GHMC fields
        'vehicle_no', 'vehicle_type', 'driver_name', 'driver_contact',
        'zone_id', 'circle_id', 'ward_id', 'transfer_station_id',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'last_location_update' => 'datetime',
        'meta' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'heading' => 'decimal:2',
        'is_moving' => 'boolean',
        'mileage_current_value' => 'decimal:2',
        'engine_hours_current_value' => 'decimal:2',
        'gprs_traffic_counter' => 'integer',
        'mileage_auto' => 'boolean',
        'engine_hours_auto' => 'boolean',
        'gprs_traffic_auto' => 'boolean',
    ];

    // Scopes for filtering devices
    public function scopeOnline($query)
    {
        return $query->where('status', 'active')
                    ->where('last_location_update', '>=', now()->subMinutes(10));
    }

    public function scopeOffline($query)
    {
        return $query->where('status', 'inactive')
                    ->orWhere('last_location_update', '<', now()->subMinutes(10));
    }

    public function scopeMoving($query)
    {
        return $query->where('is_moving', true)->where('speed', '>', 0);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function gpsData()
    {
        return $this->hasMany(GpsData::class);
    }

    public function fuelSensor()
    {
        return $this->hasOne(FuelSensor::class);
    }

    public function dashcam()
    {
        return $this->hasOne(Dashcam::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    public function transferStation()
    {
        return $this->belongsTo(TransferStation::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function latestPosition()
    {
        return $this->hasOne(Position::class)->latestOfMany('fix_time');
    }

    // Accessors
    public function getLastLocationAttribute()
    {
        return $this->latestPosition;
    }

    public function getIsOnlineAttribute()
    {
        return $this->status === 'active' && 
               $this->last_location_update && 
               $this->last_location_update->gt(now()->subHours(24));
    }

    public function getStatusDisplayAttribute()
    {
        return $this->is_online ? 'online' : 'offline';
    }
}


