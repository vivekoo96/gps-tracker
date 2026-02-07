<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Device;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Display user management page
     */
    public function index()
    {
        $users = User::with(['zoneAssignments'])->get();
        $zones = DB::table('zones')->get();
        $wards = DB::table('wards')->get();
        $transferStations = DB::table('transfer_stations')->get();
        
        return view('admin.users.index', compact('users', 'zones', 'wards', 'transferStations'));
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,executive,officer,viewer',
            'phone' => 'nullable|string|max:20',
            'zones' => 'nullable|array',
            'wards' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
        ]);

        // Assign zones and wards
        if (!empty($validated['zones'])) {
            foreach ($validated['zones'] as $zoneId) {
                DB::table('user_zone_assignments')->insert([
                    'user_id' => $user->id,
                    'zone_id' => $zoneId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        if (!empty($validated['wards'])) {
            foreach ($validated['wards'] as $wardId) {
                DB::table('user_zone_assignments')->insert([
                    'user_id' => $user->id,
                    'ward_id' => $wardId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user
        ]);
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:8',
            'role' => 'required|in:admin,executive,officer,viewer',
            'phone' => 'nullable|string|max:20',
            'zones' => 'nullable|array',
            'wards' => 'nullable|array',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update zone and ward assignments
        DB::table('user_zone_assignments')->where('user_id', $user->id)->delete();

        if (!empty($validated['zones'])) {
            foreach ($validated['zones'] as $zoneId) {
                DB::table('user_zone_assignments')->insert([
                    'user_id' => $user->id,
                    'zone_id' => $zoneId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        if (!empty($validated['wards'])) {
            foreach ($validated['wards'] as $wardId) {
                DB::table('user_zone_assignments')->insert([
                    'user_id' => $user->id,
                    'ward_id' => $wardId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get user details
     */
    public function show($id)
    {
        $user = User::with(['zoneAssignments'])->findOrFail($id);
        
        $assignments = DB::table('user_zone_assignments')
            ->where('user_id', $user->id)
            ->get();

        $assignedZones = $assignments->where('zone_id', '!=', null)->pluck('zone_id')->toArray();
        $assignedWards = $assignments->where('ward_id', '!=', null)->pluck('ward_id')->toArray();

        return response()->json([
            'success' => true,
            'user' => $user,
            'assigned_zones' => $assignedZones,
            'assigned_wards' => $assignedWards
        ]);
    }

    /**
     * Vehicle assignment page
     */
    public function vehicleAssignments()
    {
        $devices = Device::with(['zone', 'ward'])->get();
        $zones = DB::table('zones')->get();
        $wards = DB::table('wards')->get();
        $transferStations = DB::table('transfer_stations')->get();
        
        return view('admin.users.vehicle-assignments', compact('devices', 'zones', 'wards', 'transferStations'));
    }

    /**
     * Update vehicle assignment
     */
    public function updateVehicleAssignment(Request $request, $vehicleId)
    {
        $validated = $request->validate([
            'zone_id' => 'nullable|exists:zones,id',
            'ward_id' => 'nullable|exists:wards,id',
            'transfer_station_id' => 'nullable|exists:transfer_stations,id',
        ]);

        $device = Device::findOrFail($vehicleId);
        $device->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle assignment updated successfully'
        ]);
    }
}
