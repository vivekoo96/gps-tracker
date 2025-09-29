<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Device;
use App\Models\Position;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        
        // Get user's devices (if user has specific devices assigned)
        // For now, we'll show all devices for simplicity, but you can filter by user later
        $userDevices = Device::all(); // You might want to filter by user relationship
        
        // Get devices with location data for map
        $devicesWithLocation = Device::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'unique_id', 'latitude', 'longitude', 'speed', 'is_moving', 'last_location_update']);
        
        // GPS Tracking Statistics
        $totalDevices = $userDevices->count();
        $onlineDevices = $userDevices->filter(fn($device) => $device->is_online)->count();
        $offlineDevices = $totalDevices - $onlineDevices;
        $movingDevices = $userDevices->filter(fn($device) => $device->is_moving)->count();
        
        // Device status breakdown
        $lowBatteryDevices = $userDevices->filter(fn($device) => 
            isset($device->battery_level) && $device->battery_level < 20
        )->count();
        
        // Recent activity - positions and device status changes
        $recentPositions = Position::with('device')
            ->whereIn('device_id', $userDevices->pluck('id'))
            ->latest('fix_time')
            ->take(10)
            ->get();
        
        // Activity feed combining different events
        $recentActivity = collect();
        
        // Add recent position updates as activity
        foreach ($recentPositions->take(5) as $position) {
            $recentActivity->push([
                'type' => 'position_update',
                'message' => "Device {$position->device->name} updated location",
                'device' => $position->device->name ?? "GPS-{$position->device_id}",
                'time' => $position->fix_time,
                'status' => 'info'
            ]);
        }
        
        // Add device status changes
        foreach ($userDevices->take(3) as $device) {
            if ($device->is_online) {
                $recentActivity->push([
                    'type' => 'device_online',
                    'message' => "Device {$device->name} came online",
                    'device' => $device->name ?? "GPS-{$device->id}",
                    'time' => $device->last_location_update ?? $device->updated_at,
                    'status' => 'success'
                ]);
            }
            
            if (isset($device->battery_level) && $device->battery_level < 20) {
                $recentActivity->push([
                    'type' => 'low_battery',
                    'message' => "Device {$device->name} battery low ({$device->battery_level}%)",
                    'device' => $device->name ?? "GPS-{$device->id}",
                    'time' => $device->updated_at,
                    'status' => 'warning'
                ]);
            }
        }
        
        // Sort activity by time and take the most recent
        $recentActivity = $recentActivity->sortByDesc('time')->take(8);
        
        // Calculate growth percentages
        $devicesYesterday = Device::whereDate('created_at', now()->yesterday())->count();
        $devicesThisMonth = Device::whereMonth('created_at', now()->month)->count();
        $devicesLastMonth = Device::whereMonth('created_at', now()->subMonth()->month)->count();
        
        $deviceGrowthPercentage = $devicesLastMonth > 0 
            ? round((($devicesThisMonth - $devicesLastMonth) / $devicesLastMonth) * 100, 1)
            : 0;
        
        $activeDeviceGrowthPercentage = $devicesYesterday > 0 
            ? round((($onlineDevices - $devicesYesterday) / $devicesYesterday) * 100, 1)
            : 0;
        
        // Total users (for admin-like overview)
        $totalUsers = User::count();
        $usersThisWeek = User::where('created_at', '>=', now()->subWeek())->count();
        $usersLastWeek = User::where('created_at', '>=', now()->subWeeks(2))
                            ->where('created_at', '<', now()->subWeek())->count();
        
        $userGrowthPercentage = $usersLastWeek > 0 
            ? round((($usersThisWeek - $usersLastWeek) / $usersLastWeek) * 100, 1)
            : 0;
        
        // Active alerts (devices with issues)
        $criticalAlerts = $userDevices->filter(fn($device) => !$device->is_online)->count();
        $warningAlerts = $lowBatteryDevices;
        $totalAlerts = $criticalAlerts + $warningAlerts;
        
        $stats = [
            'total_devices' => $totalDevices,
            'online_devices' => $onlineDevices,
            'offline_devices' => $offlineDevices,
            'moving_devices' => $movingDevices,
            'low_battery_devices' => $lowBatteryDevices,
            'total_users' => $totalUsers,
            'recent_activity' => $recentActivity,
            'device_growth_percentage' => $deviceGrowthPercentage,
            'active_device_growth_percentage' => $activeDeviceGrowthPercentage,
            'user_growth_percentage' => $userGrowthPercentage,
            'total_alerts' => $totalAlerts,
            'critical_alerts' => $criticalAlerts,
            'warning_alerts' => $warningAlerts,
            'devices_with_location' => $devicesWithLocation,
        ];

        return view('dashboard', compact('stats'));
    }
}
