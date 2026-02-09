<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Device;
use App\Models\Position;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        // Redirect based on role
        if ($user->hasRole('vendor_admin')) {
            return redirect()->route('vendor.dashboard');
        }
        if ($user->hasRole('super_admin')) {
            return redirect()->route('super_admin.dashboard');
        }
        
        // GPS Tracking Statistics - Use database queries with actual columns
        $totalDevices = Device::count();
        
        // is_online is an accessor, not a column. Use the actual logic from the accessor
        $onlineDevices = Device::where('status', 'active')
            ->where('last_location_update', '>', now()->subHours(24))
            ->count();
            
        $offlineDevices = $totalDevices - $onlineDevices;
        $movingDevices = Device::where('is_moving', true)->count();
        $lowBatteryDevices = Device::whereNotNull('battery_level')
            ->where('battery_level', '<', 20)
            ->count();
        
        // Get devices with location data for map (paginated)
        $devicesWithLocation = Device::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select(['id', 'name', 'unique_id', 'latitude', 'longitude', 'speed', 'is_moving', 'last_location_update'])
            ->limit(100)
            ->get();
        
        // Recent activity - positions and device status changes
        $recentPositions = Position::with('device')
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
        
        // Add recent device status updates (only fetch a small sample)
        // Use actual database columns, not accessors
        // Fetch recently online devices
        $recentOnlineDevices = Device::where('status', 'active')
            ->where('last_location_update', '>', now()->subHours(24))
            ->select(['id', 'name', 'status', 'battery_level', 'last_location_update', 'updated_at'])
            ->orderBy('last_location_update', 'desc')
            ->limit(3)
            ->get();
        
        // Fetch low battery devices separately
        $lowBatteryDevicesRecent = Device::where('battery_level', '<', 20)
            ->whereNotNull('battery_level')
            ->select(['id', 'name', 'status', 'battery_level', 'last_location_update', 'updated_at'])
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();
        
        // Merge and process both collections
        $recentDevices = $recentOnlineDevices->merge($lowBatteryDevicesRecent)->unique('id');
        
        foreach ($recentDevices as $device) {
            // Check if device is online using the same logic as the accessor
            $isOnline = $device->status === 'active' && 
                       $device->last_location_update && 
                       $device->last_location_update->gt(now()->subHours(24));
                       
            if ($isOnline) {
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
        $criticalAlerts = $offlineDevices;
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
