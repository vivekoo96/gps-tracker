<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeofenceAlert extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\VendorScope);
        
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->vendor_id && !isset($model->vendor_id)) {
                $model->vendor_id = auth()->user()->vendor_id;
            }
        });
    }

    protected $fillable = [
        'geofence_id',
        'alert_on_entry',
        'alert_on_exit',
        'notify_users',
        'email_enabled',
        'sms_enabled',
    ];

    protected $casts = [
        'notify_users' => 'array',
        'alert_on_entry' => 'boolean',
        'alert_on_exit' => 'boolean',
        'email_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
    ];

    /**
     * Get the geofence this alert belongs to
     */
    public function geofence()
    {
        return $this->belongsTo(Geofence::class);
    }

    /**
     * Check if alerts should be sent for a specific event type
     *
     * @param string $eventType 'enter' or 'exit'
     * @return bool
     */
    public function shouldAlert($eventType)
    {
        if ($eventType === 'enter') {
            return $this->alert_on_entry;
        }
        
        if ($eventType === 'exit') {
            return $this->alert_on_exit;
        }
        
        return false;
    }

    /**
     * Get users to notify
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersToNotify()
    {
        if (empty($this->notify_users)) {
            return collect();
        }

        return User::whereIn('id', $this->notify_users)->get();
    }
}
