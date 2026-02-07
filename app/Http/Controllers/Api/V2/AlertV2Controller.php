<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Alert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertV2Controller extends BaseV2Controller
{
    /**
     * Display a listing of alerts.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Alert::query();

        if ($request->has('device_id')) {
            $query->where('device_id', $request->get('device_id'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->has('severity')) {
            $query->where('severity', $request->get('severity'));
        }

        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        return $this->paginate($request, $query);
    }

    /**
     * Mark an alert as read/acknowledged.
     */
    public function acknowledge(Request $request, string $id): JsonResponse
    {
        $alert = Alert::find($id);

        if (!$alert) {
            return $this->error('Alert not found', 'NOT_FOUND', 404);
        }

        $alert->update([
            'is_read' => true,
            'read_at' => now(),
            'read_by' => auth()->id(),
        ]);

        return $this->success($alert, 'Alert acknowledged successfully');
    }
}
