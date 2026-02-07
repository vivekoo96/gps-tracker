<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverViolation;
use App\Models\DriverScore;
use App\Models\DriverAlert;
use App\Models\User;
use App\Services\DriverScoringService;
use Illuminate\Http\Request;

class DriverBehaviorController extends Controller
{
    protected $scoringService;

    public function __construct(DriverScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    /**
     * Dashboard overview
     */
    public function index()
    {
        $vendorId = auth()->user()->vendor_id;

        $stats = [
            'total_violations' => DriverViolation::where('vendor_id', $vendorId)->count(),
            'critical_violations' => DriverViolation::where('vendor_id', $vendorId)
                ->where('severity', 'critical')->count(),
            'avg_score' => DriverScore::where('vendor_id', $vendorId)
                ->where('period', 'daily')->avg('score'),
            'unread_alerts' => DriverAlert::where('vendor_id', $vendorId)
                ->where('is_read', false)->count(),
        ];

        $recentViolations = DriverViolation::with(['driver', 'device'])
            ->where('vendor_id', $vendorId)
            ->latest('occurred_at')
            ->limit(10)
            ->get();

        $topDrivers = DriverScore::with('driver')
            ->where('vendor_id', $vendorId)
            ->where('period', 'daily')
            ->orderBy('score', 'desc')
            ->limit(5)
            ->get();

        $bottomDrivers = DriverScore::with('driver')
            ->where('vendor_id', $vendorId)
            ->where('period', 'daily')
            ->orderBy('score', 'asc')
            ->limit(5)
            ->get();

        return view('admin.driver-behavior.index', compact('stats', 'recentViolations', 'topDrivers', 'bottomDrivers'));
    }

    /**
     * Violations list
     */
    public function violations(Request $request)
    {
        $vendorId = auth()->user()->vendor_id;

        $query = DriverViolation::with(['driver', 'device'])
            ->where('vendor_id', $vendorId);

        // Filters
        if ($request->type) {
            $query->where('violation_type', $request->type);
        }
        if ($request->severity) {
            $query->where('severity', $request->severity);
        }
        if ($request->driver_id) {
            $query->where('driver_id', $request->driver_id);
        }
        if ($request->date_from) {
            $query->where('occurred_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->where('occurred_at', '<=', $request->date_to);
        }

        $violations = $query->latest('occurred_at')->paginate(20);

        $drivers = User::where('vendor_id', $vendorId)->get();

        return view('admin.driver-behavior.violations', compact('violations', 'drivers'));
    }

    /**
     * Violation details
     */
    public function violationDetails($id)
    {
        $violation = DriverViolation::with(['driver', 'device', 'acknowledgedBy'])
            ->findOrFail($id);

        return view('admin.driver-behavior.violation-details', compact('violation'));
    }

    /**
     * Acknowledge violation
     */
    public function acknowledgeViolation(Request $request, $id)
    {
        $violation = DriverViolation::findOrFail($id);
        $violation->acknowledge(auth()->id(), $request->notes);

        return back()->with('success', 'Violation acknowledged successfully');
    }

    /**
     * Driver leaderboard
     */
    public function leaderboard(Request $request)
    {
        $vendorId = auth()->user()->vendor_id;
        $period = $request->get('period', 'daily');

        $scores = DriverScore::with('driver')
            ->where('vendor_id', $vendorId)
            ->where('period', $period)
            ->orderBy('rank', 'asc')
            ->paginate(50);

        return view('admin.driver-behavior.leaderboard', compact('scores', 'period'));
    }

    /**
     * Driver profile
     */
    public function driverProfile($driverId)
    {
        $driver = User::findOrFail($driverId);

        $currentScore = DriverScore::where('driver_id', $driverId)
            ->where('period', 'daily')
            ->latest('period_start')
            ->first();

        $recentViolations = DriverViolation::where('driver_id', $driverId)
            ->latest('occurred_at')
            ->limit(10)
            ->get();

        $scoreHistory = DriverScore::where('driver_id', $driverId)
            ->where('period', 'daily')
            ->orderBy('period_start', 'desc')
            ->limit(30)
            ->get();

        return view('admin.driver-behavior.driver-profile', compact('driver', 'currentScore', 'recentViolations', 'scoreHistory'));
    }

    /**
     * Alerts management
     */
    public function alerts()
    {
        $vendorId = auth()->user()->vendor_id;

        $alerts = DriverAlert::with(['driver', 'device'])
            ->where('vendor_id', $vendorId)
            ->latest('sent_at')
            ->paginate(20);

        return view('admin.driver-behavior.alerts', compact('alerts'));
    }

    /**
     * Mark alert as read
     */
    public function markAlertRead($id)
    {
        $alert = DriverAlert::findOrFail($id);
        $alert->markAsRead();

        return response()->json(['success' => true]);
    }
}
