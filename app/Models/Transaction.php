<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'vendor_id',
        'subscription_plan_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'amount',
        'currency',
        'status', // created, paid, failed
        'method',
        'receipt'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
}
