<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'company_name', 'subdomain', 'email', 'phone', 'address', 'logo', 'primary_color',
        'subscription_plan_id', 'subscription_expires_at', 'status'
    ];

    protected $casts = [
        'subscription_expires_at' => 'datetime',
    ];

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
