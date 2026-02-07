<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmergencyContact;
use App\Models\Device;
use Illuminate\Http\Request;

class EmergencyContactController extends Controller
{
    /**
     * Display emergency contacts for a device
     */
    public function index($deviceId)
    {
        $device = Device::findOrFail($deviceId);
        $contacts = EmergencyContact::where('device_id', $deviceId)
            ->orderBy('priority', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'device' => $device,
            'contacts' => $contacts
        ]);
    }

    /**
     * Store a new emergency contact
     */
    public function store(Request $request, $deviceId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'priority' => 'required|integer|min:1',
            'notify_sms' => 'boolean',
            'notify_email' => 'boolean'
        ]);

        $contact = EmergencyContact::create([
            'device_id' => $deviceId,
            ...$validated
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Emergency contact added',
            'contact' => $contact
        ]);
    }

    /**
     * Update an emergency contact
     */
    public function update(Request $request, $deviceId, $id)
    {
        $contact = EmergencyContact::where('device_id', $deviceId)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'phone' => 'string|max:20',
            'email' => 'nullable|email',
            'priority' => 'integer|min:1',
            'notify_sms' => 'boolean',
            'notify_email' => 'boolean'
        ]);

        $contact->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Emergency contact updated',
            'contact' => $contact->fresh()
        ]);
    }

    /**
     * Delete an emergency contact
     */
    public function destroy($deviceId, $id)
    {
        $contact = EmergencyContact::where('device_id', $deviceId)
            ->findOrFail($id);

        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Emergency contact deleted'
        ]);
    }
}
