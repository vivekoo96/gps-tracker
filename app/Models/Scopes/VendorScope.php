<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class VendorScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Recursion guard to prevent infinite loops when auth()->user() 
        // triggers another VendorScope application.
        static $isApplying = false;
        if ($isApplying) return;
        
        $isApplying = true;

        try {
            // 1. If we've identified a vendor by subdomain (highest priority)
            if (app()->has('current_vendor')) {
                $builder->where($model->getTable() . '.vendor_id', app('current_vendor')->id);
                return;
            }

            // 2. Otherwise fall back to logged-in user
            if (auth()->check()) {
                $user = auth()->user();
                
                // If user is super admin, don't apply any filter
                if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                    return;
                }

                // If user has a vendor_id, filter by it
                if ($user && isset($user->vendor_id)) {
                    $builder->where($model->getTable() . '.vendor_id', $user->vendor_id);
                } else {
                    // FALLBACK SECURE: Non-super-admin without vendor_id sees NOTHING
                    $builder->whereRaw('1 = 0');
                }
            }
        } finally {
            $isApplying = false;
        }
    }
}
