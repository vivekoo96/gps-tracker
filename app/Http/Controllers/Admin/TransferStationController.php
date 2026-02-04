<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransferStation;
use App\Models\Ward;
use Illuminate\Http\Request;

class TransferStationController extends Controller
{
    public function index()
    {
        $transferStations = TransferStation::with(['ward.circle.zone'])->withCount('devices')->paginate(10);
        return view('admin.transfer_stations.index', compact('transferStations'));
    }

    public function create()
    {
        $wards = Ward::with('circle.zone')->get()->sortBy('circle.zone.name');
        return view('admin.transfer_stations.create', compact('wards'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ward_id' => 'required|exists:wards,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'capacity' => 'required|numeric|min:0',
            'current_load' => 'nullable|numeric|min:0',
        ]);

        if (TransferStation::where('ward_id', $validated['ward_id'])->where('name', $validated['name'])->exists()) {
             return back()->withErrors(['name' => 'The transfer station name has already been taken in this ward.'])->withInput();
        }

        TransferStation::create($validated);

        return redirect()->route('admin.transfer-stations.index')
            ->with('success', 'Transfer Station created successfully.');
    }

    public function show(TransferStation $transferStation)
    {
        return view('admin.transfer_stations.show', compact('transferStation'));
    }

    public function edit(TransferStation $transferStation)
    {
        $wards = Ward::with('circle.zone')->get()->sortBy('circle.zone.name');
        return view('admin.transfer_stations.edit', compact('transferStation', 'wards'));
    }

    public function update(Request $request, TransferStation $transferStation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ward_id' => 'required|exists:wards,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'capacity' => 'required|numeric|min:0',
            'current_load' => 'nullable|numeric|min:0',
        ]);

        if (TransferStation::where('ward_id', $validated['ward_id'])
                  ->where('name', $validated['name'])
                  ->where('id', '!=', $transferStation->id)
                  ->exists()) {
             return back()->withErrors(['name' => 'The transfer station name has already been taken in this ward.'])->withInput();
        }

        $transferStation->update($validated);

        return redirect()->route('admin.transfer-stations.index')
            ->with('success', 'Transfer Station updated successfully.');
    }

    public function destroy(TransferStation $transferStation)
    {
        $transferStation->delete();

        return redirect()->route('admin.transfer-stations.index')
            ->with('success', 'Transfer Station deleted successfully.');
    }
}
