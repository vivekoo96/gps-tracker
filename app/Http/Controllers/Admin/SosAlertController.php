<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SosAlert;
use App\Services\SosNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SosAlertController extends Controller
{
    protected $notificationService;

    public function __construct(SosNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display all SOS alerts
     */
    public function index(Request $request)
    {
        $query = SosAlert::with(['device', 'acknowledgedByUser'])
            ->orderBy('triggered_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by device
        if ($request->has('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        $alerts = $query->paginate(20);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'alerts' => $alerts
            ]);
        }

        return view('admin.sos-alerts.index', compact('alerts'));
    }

    /**
     * Acknowledge an SOS alert
     */
    public function acknowledge(Request $request, $id)
    {
        $alert = SosAlert::findOrFail($id);
        
        $alert->acknowledge(Auth::id(), $request->input('notes'));

        return response()->json([
            'success' => true,
            'message' => 'SOS alert acknowledged',
            'alert' => $alert->fresh()
        ]);
    }

    /**
     * Resolve an SOS alert
     */
    public function resolve(Request $request, $id)
    {
        $alert = SosAlert::findOrFail($id);
        
        $alert->resolve(Auth::id(), $request->input('notes'));

        return response()->json([
            'success' => true,
            'message' => 'SOS alert resolved',
            'alert' => $alert->fresh()
        ]);
    }

    /**
     * Mark as false alarm
     */
    public function falseAlarm(Request $request, $id)
    {
        $alert = SosAlert::findOrFail($id);
        
        $alert->markAsFalseAlarm(Auth::id(), $request->input('notes'));

        return response()->json([
            'success' => true,
            'message' => 'Marked as false alarm',
            'alert' => $alert->fresh()
        ]);
    }

    /**
     * Resend notifications
     */
    public function resendNotifications($id)
    {
        $alert = SosAlert::findOrFail($id);
        
        $this->notificationService->sendNotifications($alert);

        return response()->json([
            'success' => true,
            'message' => 'Notifications sent'
        ]);
    }
}
