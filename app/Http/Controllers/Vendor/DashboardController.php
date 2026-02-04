<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\Device;

class DashboardController extends Controller
{
    public function index()
    {
        $vendor = Auth::user()->vendor;
        
        $stats = [
            'total_devices' => $vendor ? $vendor->devices()->count() : 0,
            'online_devices' => $vendor ? $vendor->devices()->online()->count() : 0,
            'offline_devices' => $vendor ? $vendor->devices()->offline()->count() : 0,
            'subscription' => $vendor ? $vendor->subscriptionPlan->name : 'N/A',
        ];

        return view('vendor.dashboard', compact('stats'));
    }
}
