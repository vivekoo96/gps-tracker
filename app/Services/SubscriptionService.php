<?php

namespace App\Services;

use App\Models\Vendor;
use App\Models\SubscriptionPlan;

class SubscriptionService
{
    /**
     * Check if vendor can create more devices.
     */
    public function canCreateDevice(Vendor $vendor): bool
    {
        if (!$vendor->subscriptionPlan) {
            return false;
        }

        // Assuming subscription_plans table has 'max_devices' column
        // If 0 or null, assume unlimited (or strict 0 depending on logic, usually 0 means strict)
        // Let's assume -1 is unlimited
        $limit = $vendor->subscriptionPlan->max_devices;

        if ($limit === -1) {
            return true;
        }

        return $vendor->devices()->count() < $limit;
    }

    /**
     * Check if vendor can create more users.
     */
    public function canCreateUser(Vendor $vendor): bool
    {
        if (!$vendor->subscriptionPlan) {
            return false;
        }

        $limit = $vendor->subscriptionPlan->max_users;

        if ($limit === -1) {
            return true;
        }

        // Count users excluding the vendor admin themself if needed, 
        // but typically we count all users in the vendor_id
        return $vendor->users()->count() < $limit;
    }
}
