<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\GeofenceAlert;
use App\Models\SosAlert;
use App\Models\DriverViolation;
use App\Models\FuelAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AlertV2Controller extends BaseV2Controller
{
    /**
     * Display a listing of alerts from all sources.
     */
    public function index(Request $request): JsonResponse
    {
        $alerts = collect();
        
        // Gather geofence alerts
        $geofenceAlerts = GeofenceAlert::with('device')
            ->when($request->has('device_id'), fn($q) => $q->where('device_id', $request->device_id))
            ->when($request->has('is_read'), fn($q) => $q->where('is_read', $request->boolean('is_read')))
            ->latest('created_at')
            ->get()
            ->map(fn($alert) => [
                'id' => $alert->id,
                'type' => 'geofence',
                'device_id' => $alert->device_id,
                'device_name' => $alert->device->name ?? null,
                'severity' => $alert->severity ?? 'medium',
                'message' => $alert->alert_type . ' - ' . $alert->geofence_name,
                'latitude' => $alert->latitude,
                'longitude' => $alert->longitude,
                'is_read' => $alert->is_read ?? false,
                'created_at' => $alert->created_at,
            ]);
        
        // Gather SOS alerts
        $sosAlerts = SosAlert::with('device')
            ->when($request->has('device_id'), fn($q) => $q->where('device_id', $request->device_id))
            ->latest('triggered_at')
            ->get()
            ->map(fn($alert) => [
                'id' => $alert->id,
                'type' => 'sos',
                'device_id' => $alert->device_id,
                'device_name' => $alert->device->name ?? null,
                'severity' => 'critical',
                'message' => 'SOS Alert Triggered',
                'latitude' => $alert->latitude,
                'longitude' => $alert->longitude,
                'is_read' => $alert->status === 'resolved',
                'created_at' => $alert->triggered_at,
            ]);
        
        // Gather driver violations
        $violations = DriverViolation::with('device')
            ->when($request->has('device_id'), fn($q) => $q->where('device_id', $request->device_id))
            ->when($request->has('severity'), fn($q) => $q->where('severity', $request->severity))
            ->latest('occurred_at')
            ->get()
            ->map(fn($alert) => [
                'id' => $alert->id,
                'type' => 'violation',
                'device_id' => $alert->device_id,
                'device_name' => $alert->device->name ?? null,
                'severity' => $alert->severity,
                'message' => ucfirst(str_replace('_', ' ', $alert->violation_type)),
                'latitude' => $alert->latitude,
                'longitude' => $alert->longitude,
                'is_read' => false,
                'created_at' => $alert->occurred_at,
            ]);
        
        // Merge all alerts
        $alerts = $geofenceAlerts->concat($sosAlerts)->concat($violations);
        
        // Sort by created_at descending
        $alerts = $alerts->sortByDesc('created_at')->values();
        
        // Paginate manually
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);
        $total = $alerts->count();
        
        $paginatedAlerts = $alerts->slice(($page - 1) * $perPage, $perPage)->values();
        
        return response()->json([
            'status' => 'success',
            'data' => $paginatedAlerts,
            'meta' => [
                'current_page' => (int)$page,
                'per_page' => (int)$perPage,
                'total' => $total,
                'last_page' => (int)ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Mark an alert as read/acknowledged.
     */
    public function acknowledge(Request $request, string $id): JsonResponse
    {
        // Try to find in different alert tables
        $alert = GeofenceAlert::find($id) ?? SosAlert::find($id);

        if (!$alert) {
            return $this->error('Alert not found', 'NOT_FOUND', 404);
        }

        if ($alert instanceof GeofenceAlert) {
            $alert->update(['is_read' => true]);
        } elseif ($alert instanceof SosAlert) {
            $alert->update(['status' => 'resolved']);
        }

        return $this->success($alert, 'Alert acknowledged successfully');
    }
}
