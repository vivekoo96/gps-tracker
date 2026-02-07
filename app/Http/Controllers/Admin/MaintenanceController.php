<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceRecord;
use App\Models\MaintenancePart;
use App\Models\MaintenanceReminder;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index()
    {
        $vendorId = auth()->user()->vendor_id;

        $stats = [
            'total_cost' => MaintenanceRecord::where('vendor_id', $vendorId)
                ->whereMonth('service_date', now()->month)
                ->sum('cost'),
            'overdue_services' => MaintenanceReminder::where('vendor_id', $vendorId)
                ->where('reminder_type', 'overdue')
                ->where('is_acknowledged', false)
                ->count(),
            'upcoming_services' => MaintenanceReminder::where('vendor_id', $vendorId)
                ->where('reminder_type', 'upcoming')
                ->where('is_acknowledged', false)
                ->count(),
            'low_stock_parts' => MaintenancePart::where('vendor_id', $vendorId)
                ->lowStock()
                ->count(),
        ];

        $recentRecords = MaintenanceRecord::with('device')
            ->where('vendor_id', $vendorId)
            ->latest('service_date')
            ->limit(10)
            ->get();

        $activeSchedules = MaintenanceSchedule::with('device')
            ->where('vendor_id', $vendorId)
            ->active()
            ->limit(10)
            ->get();

        $overdueReminders = MaintenanceReminder::with('device')
            ->where('vendor_id', $vendorId)
            ->where('reminder_type', 'overdue')
            ->unacknowledged()
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.maintenance.index', compact('stats', 'recentRecords', 'activeSchedules', 'overdueReminders'));
    }

    public function schedules()
    {
        $vendorId = auth()->user()->vendor_id;
        
        $schedules = MaintenanceSchedule::with('device')
            ->where('vendor_id', $vendorId)
            ->paginate(20);

        return view('admin.maintenance.schedules', compact('schedules'));
    }

    public function history()
    {
        $vendorId = auth()->user()->vendor_id;
        
        $records = MaintenanceRecord::with('device')
            ->where('vendor_id', $vendorId)
            ->latest('service_date')
            ->paginate(20);

        return view('admin.maintenance.history', compact('records'));
    }

    public function parts()
    {
        $vendorId = auth()->user()->vendor_id;
        
        $parts = MaintenancePart::where('vendor_id', $vendorId)
            ->paginate(20);

        return view('admin.maintenance.parts', compact('parts'));
    }

    public function reminders()
    {
        $vendorId = auth()->user()->vendor_id;
        
        $reminders = MaintenanceReminder::with('device')
            ->where('vendor_id', $vendorId)
            ->latest()
            ->paginate(20);

        return view('admin.maintenance.reminders', compact('reminders'));
    }
}
