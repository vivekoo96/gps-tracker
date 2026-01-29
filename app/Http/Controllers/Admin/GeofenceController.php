<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Geofence;
use App\Models\GeofenceAlert;
use App\Models\GeofenceEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GeofenceController extends Controller
{
    /**
     * Display a listing of geofences
     */
    public function index()
    {
        $geofences = Geofence::with(['creator', 'events' => function($query) {
            $query->whereDate('event_time', today());
        }])->latest()->get();

        $stats = [
            'total' => $geofences->count(),
            'active' => $geofences->where('is_active', true)->count(),
            'events_today' => GeofenceEvent::whereDate('event_time', today())->count(),
        ];

        return view('admin.geofences.index', compact('geofences', 'stats'));
    }

    /**
     * Show the form for creating a new geofence
     */
    public function create()
    {
        $users = User::all();
        return view('admin.geofences.create', compact('users'));
    }

    /**
     * Store a newly created geofence
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:50000',
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'is_active' => 'boolean',
            'alert_on_entry' => 'boolean',
            'alert_on_exit' => 'boolean',
            'notify_users' => 'nullable|array',
            'notify_users.*' => 'exists:users,id',
        ]);

        $geofence = Geofence::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'radius' => $validated['radius'],
            'color' => $validated['color'],
            'is_active' => $validated['is_active'] ?? true,
            'created_by' => Auth::id(),
        ]);

        // Create alert configuration
        GeofenceAlert::create([
            'geofence_id' => $geofence->id,
            'alert_on_entry' => $validated['alert_on_entry'] ?? true,
            'alert_on_exit' => $validated['alert_on_exit'] ?? true,
            'notify_users' => $validated['notify_users'] ?? [],
        ]);

        return redirect()->route('admin.geofences.index')
            ->with('status', "Geofence '{$geofence->name}' created successfully!");
    }

    /**
     * Show the form for editing a geofence
     */
    public function edit(Geofence $geofence)
    {
        $geofence->load('alert');
        $users = User::all();
        return view('admin.geofences.edit', compact('geofence', 'users'));
    }

    /**
     * Update the specified geofence
     */
    public function update(Request $request, Geofence $geofence)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:50000',
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'is_active' => 'boolean',
            'alert_on_entry' => 'boolean',
            'alert_on_exit' => 'boolean',
            'notify_users' => 'nullable|array',
            'notify_users.*' => 'exists:users,id',
        ]);

        $geofence->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'radius' => $validated['radius'],
            'color' => $validated['color'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Update or create alert configuration
        $geofence->alert()->updateOrCreate(
            ['geofence_id' => $geofence->id],
            [
                'alert_on_entry' => $validated['alert_on_entry'] ?? true,
                'alert_on_exit' => $validated['alert_on_exit'] ?? true,
                'notify_users' => $validated['notify_users'] ?? [],
            ]
        );

        return redirect()->route('admin.geofences.index')
            ->with('status', "Geofence '{$geofence->name}' updated successfully!");
    }

    /**
     * Remove the specified geofence
     */
    public function destroy(Geofence $geofence)
    {
        $name = $geofence->name;
        $geofence->delete();

        return redirect()->route('admin.geofences.index')
            ->with('status', "Geofence '{$name}' deleted successfully!");
    }

    /**
     * Show events for a specific geofence
     */
    public function events(Geofence $geofence)
    {
        $events = $geofence->events()
            ->with('device')
            ->orderBy('event_time', 'desc')
            ->paginate(50);

        $stats = [
            'total_events' => $geofence->events()->count(),
            'entries' => $geofence->events()->where('event_type', 'enter')->count(),
            'exits' => $geofence->events()->where('event_type', 'exit')->count(),
            'today' => $geofence->events()->whereDate('event_time', today())->count(),
        ];

        return view('admin.geofences.events', compact('geofence', 'events', 'stats'));
    }
}
