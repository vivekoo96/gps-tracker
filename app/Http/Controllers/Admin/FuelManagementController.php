<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FuelTransaction;
use App\Models\FuelEfficiencyReport;
use App\Models\FuelAlert;
use App\Models\Device;
use App\Models\User;
use App\Services\FuelEfficiencyService;
use Illuminate\Http\Request;

class FuelManagementController extends Controller
{
    public function __construct(
        protected FuelEfficiencyService $efficiencyService
    ) {}

    /**
     * Dashboard overview
     */
    public function index()
    {
        $vendorId = auth()->user()->vendor_id;

        $stats = [
            'total_fuel_consumed' => FuelTransaction::where('vendor_id', $vendorId)
                ->where('transaction_type', 'consumption')
                ->whereMonth('detected_at', now()->month)
                ->sum('fuel_change'),
            'avg_efficiency' => FuelEfficiencyReport::where('vendor_id', $vendorId)
                ->where('period', 'daily')
                ->whereMonth('period_start', now()->month)
                ->avg('average_efficiency'),
            'total_refuel_cost' => FuelTransaction::where('vendor_id', $vendorId)
                ->where('transaction_type', 'refuel')
                ->whereMonth('detected_at', now()->month)
                ->sum('cost'),
            'active_alerts' => FuelAlert::where('vendor_id', $vendorId)
                ->where('is_read', false)
                ->count(),
        ];

        $recentTransactions = FuelTransaction::with(['device'])
            ->where('vendor_id', $vendorId)
            ->latest('detected_at')
            ->limit(10)
            ->get();

        $topDevices = FuelEfficiencyReport::with('device')
            ->where('vendor_id', $vendorId)
            ->where('period', 'monthly')
            ->whereMonth('period_start', now()->month)
            ->orderBy('average_efficiency', 'desc')
            ->limit(5)
            ->get();

        return view('admin.fuel.index', compact('stats', 'recentTransactions', 'topDevices'));
    }

    /**
     * Transactions list
     */
    public function transactions(Request $request)
    {
        $vendorId = auth()->user()->vendor_id;

        $query = FuelTransaction::with(['device'])
            ->where('vendor_id', $vendorId);

        // Filters
        if ($request->filled('type')) {
            $query->where('transaction_type', $request->type);
        }

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('detected_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('detected_at', '<=', $request->date_to);
        }

        $transactions = $query->latest('detected_at')->paginate(20);
        $devices = Device::where('vendor_id', $vendorId)->get();

        return view('admin.fuel.transactions', compact('transactions', 'devices'));
    }

    /**
     * Transaction details
     */
    public function show(FuelTransaction $transaction)
    {
        $transaction->load(['device', 'confirmedBy']);
        return view('admin.fuel.show', compact('transaction'));
    }

    /**
     * Confirm transaction
     */
    public function confirm(Request $request, FuelTransaction $transaction)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $transaction->confirm(auth()->id(), $request->notes);

        return redirect()->route('admin.fuel.transactions')
            ->with('success', 'Transaction confirmed successfully');
    }

    /**
     * Efficiency reports
     */
    public function efficiency(Request $request)
    {
        $vendorId = auth()->user()->vendor_id;
        $period = $request->get('period', 'daily');

        $reports = FuelEfficiencyReport::with('device')
            ->where('vendor_id', $vendorId)
            ->where('period', $period)
            ->whereMonth('period_start', now()->month)
            ->orderBy('average_efficiency', 'desc')
            ->paginate(20);

        return view('admin.fuel.efficiency', compact('reports', 'period'));
    }

    /**
     * Fuel alerts
     */
    public function alerts()
    {
        $vendorId = auth()->user()->vendor_id;

        $alerts = FuelAlert::with('device')
            ->where('vendor_id', $vendorId)
            ->latest('sent_at')
            ->paginate(20);

        return view('admin.fuel.alerts', compact('alerts'));
    }

    /**
     * Analytics dashboard
     */
    public function analytics()
    {
        $vendorId = auth()->user()->vendor_id;

        // Get consumption data for chart
        $consumptionData = FuelTransaction::where('vendor_id', $vendorId)
            ->where('transaction_type', 'consumption')
            ->whereMonth('detected_at', now()->month)
            ->selectRaw('DATE(detected_at) as date, SUM(ABS(fuel_change)) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.fuel.analytics', compact('consumptionData'));
    }
}
