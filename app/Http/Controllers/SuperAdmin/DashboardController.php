<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor; // Assuming Vendor model exists, or check relation
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Stats Counters
        $totalVendors = User::whereHas('roles', function($q) {
            $q->where('name', 'vendor_admin');
        })->count();
        
        $totalUsers = User::count();
        $totalDevices = \App\Models\Device::count();
        $totalFuelSensors = \App\Models\FuelSensor::count();
        $totalDashcams = \App\Models\Dashcam::count();

        // 2. Chart Data Helper Function
        $getGrowthData = function ($model) {
            $data = $model::select(DB::raw('COUNT(*) as count'), DB::raw('MONTH(created_at) as month'))
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();
            
            $chart = [];
            for ($i = 1; $i <= 12; $i++) {
                $chart[] = $data[$i] ?? 0;
            }
            return $chart;
        };

        // 3. Prepare Chart Datasets
        $userGrowth = $getGrowthData(User::class);
        $deviceGrowth = $getGrowthData(\App\Models\Device::class);
        $fuelGrowth = $getGrowthData(\App\Models\FuelSensor::class);
        // Dashcams might likely follow devices or have their own growth
        $dashcamGrowth = $getGrowthData(\App\Models\Dashcam::class);

        return view('super-admin.dashboard', compact(
            'totalVendors', 
            'totalUsers', 
            'totalDevices', 
            'totalFuelSensors', 
            'totalDashcams', 
            'userGrowth',
            'deviceGrowth',
            'fuelGrowth',
            'dashcamGrowth'
        ));
    }
}
