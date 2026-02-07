<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyContact extends Model
{
    protected $fillable = [
        'device_id',
        'name',
        'phone',
        'email',
        'priority',
        'notify_sms',
        'notify_email'
    ];

    protected $casts = [
        'notify_sms' => 'boolean',
        'notify_email' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Get formatted phone number for SMS (with +91 prefix)
     */
    public function getFormattedPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        
        // Add +91 if not present
        if (!str_starts_with($phone, '91')) {
            $phone = '91' . $phone;
        }
        
        return $phone;
    }
}
