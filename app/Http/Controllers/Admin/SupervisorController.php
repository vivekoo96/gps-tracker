<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ward;
use App\Models\Ticket;
use App\Models\Device;
use Illuminate\Http\Request;

class SupervisorController extends Controller
{
    public function dashboard(Request $request)
    {
        $wards = Ward::all();
        $selectedWardId = $request->get('ward_id', $wards->first()->id ?? null);
        
        $ward = null;
        $stats = [];
        $recentTickets = [];

        if ($selectedWardId) {
            $ward = Ward::with(['circle', 'transferStations', 'devices.latestPosition'])->find($selectedWardId);
            
            $stats = [
                'total_vehicles' => $ward->devices->count(),
                'online_vehicles' => $ward->devices->filter(fn($d) => $d->is_online)->count(),
                'open_tickets' => Ticket::whereIn('device_id', $ward->devices->pluck('id'))
                    ->where('status', 'OPEN')
                    ->count(),
                'completed_trips_today' => 0, // Mock for now
                'avg_waste_status' => $ward->transferStations->avg('waste_percentage') ?? 0,
            ];

            $recentTickets = Ticket::whereIn('device_id', $ward->devices->pluck('id'))
                ->latest()
                ->take(5)
                ->get();
        }

        return view('admin.supervisor.dashboard', compact('wards', 'ward', 'stats', 'recentTickets', 'selectedWardId'));
    }
}
