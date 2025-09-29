<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Device;
use App\Models\Position;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function index(): View
    {
        // GPS Tracking Statistics
        $totalDevices = Device::count();
        $onlineDevices = Device::online()->count();
        $offlineDevices = Device::offline()->count();
        $movingDevices = Device::moving()->count();
        
        // Recent position updates
        $recentPositions = Position::with('device')
            ->latest('fix_time')
            ->take(10)
            ->get();
        
        // Device statistics for this month vs last month
        $devicesThisMonth = Device::whereMonth('created_at', now()->month)->count();
        $devicesLastMonth = Device::whereMonth('created_at', now()->subMonth()->month)->count();
        
        // Position updates today
        $positionsToday = Position::whereDate('fix_time', now()->today())->count();
        $positionsYesterday = Position::whereDate('fix_time', now()->yesterday())->count();
        
        // Recent devices
        $recentDevices = Device::latest()->take(5)->get();
        
        $stats = [
            // GPS Tracking Stats
            'total_devices' => $totalDevices,
            'online_devices' => $onlineDevices,
            'offline_devices' => $offlineDevices,
            'moving_devices' => $movingDevices,
            'recent_devices' => $recentDevices,
            'recent_positions' => $recentPositions,
            'positions_today' => $positionsToday,
            'positions_yesterday' => $positionsYesterday,
            'devices_this_month' => $devicesThisMonth,
            'devices_last_month' => $devicesLastMonth,
            
            // User Management Stats (keeping for admin purposes)
            'total_users' => User::count(),
            'active_users' => User::whereNotNull('email_verified_at')->count(),
            'total_roles' => Role::count(),
            'recent_users' => User::latest()->take(5)->get(),
            'user_registrations_this_month' => User::whereMonth('created_at', now()->month)->count(),
            'user_registrations_last_month' => User::whereMonth('created_at', now()->subMonth()->month)->count(),
        ];

        // Calculate growth percentages
        $stats['user_growth_percentage'] = $stats['user_registrations_last_month'] > 0 
            ? round((($stats['user_registrations_this_month'] - $stats['user_registrations_last_month']) / $stats['user_registrations_last_month']) * 100, 1)
            : 0;
            
        $stats['device_growth_percentage'] = $stats['devices_last_month'] > 0 
            ? round((($stats['devices_this_month'] - $stats['devices_last_month']) / $stats['devices_last_month']) * 100, 1)
            : 0;
            
        $stats['position_growth_percentage'] = $stats['positions_yesterday'] > 0 
            ? round((($stats['positions_today'] - $stats['positions_yesterday']) / $stats['positions_yesterday']) * 100, 1)
            : 0;

        return view('admin.dashboard', compact('stats'));
    }
}


