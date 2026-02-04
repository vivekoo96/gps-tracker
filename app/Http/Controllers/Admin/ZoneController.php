<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index()
    {
        $zones = Zone::withCount(['circles', 'devices'])->paginate(10);
        return view('admin.zones.index', compact('zones'));
    }

    public function create()
    {
        return view('admin.zones.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:zones',
            'description' => 'nullable|string',
        ]);

        Zone::create($validated);

        return redirect()->route('admin.zones.index')
            ->with('success', 'Zone created successfully.');
    }

    public function show(Zone $zone)
    {
        return view('admin.zones.show', compact('zone'));
    }

    public function edit(Zone $zone)
    {
        return view('admin.zones.edit', compact('zone'));
    }

    public function update(Request $request, Zone $zone)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:zones,name,' . $zone->id,
            'description' => 'nullable|string',
        ]);

        $zone->update($validated);

        return redirect()->route('admin.zones.index')
            ->with('success', 'Zone updated successfully.');
    }

    public function destroy(Zone $zone)
    {
        $zone->delete();

        return redirect()->route('admin.zones.index')
            ->with('success', 'Zone deleted successfully.');
    }
}
