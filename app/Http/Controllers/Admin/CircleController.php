<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Circle;
use App\Models\Zone;
use Illuminate\Http\Request;

class CircleController extends Controller
{
    public function index()
    {
        $circles = Circle::with(['zone'])->withCount(['wards', 'devices'])->paginate(10);
        return view('admin.circles.index', compact('circles'));
    }

    public function create()
    {
        $zones = Zone::all();
        return view('admin.circles.create', compact('zones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'zone_id' => 'required|exists:zones,id',
            'description' => 'nullable|string',
        ]);
        
        // Unique check for name within zone
        if (Circle::where('zone_id', $validated['zone_id'])->where('name', $validated['name'])->exists()) {
             return back()->withErrors(['name' => 'The circle name has already been taken in this zone.'])->withInput();
        }

        Circle::create($validated);

        return redirect()->route('admin.circles.index')
            ->with('success', 'Circle created successfully.');
    }

    public function show(Circle $circle)
    {
        return view('admin.circles.show', compact('circle'));
    }

    public function edit(Circle $circle)
    {
        $zones = Zone::all();
        return view('admin.circles.edit', compact('circle', 'zones'));
    }

    public function update(Request $request, Circle $circle)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'zone_id' => 'required|exists:zones,id',
            'description' => 'nullable|string',
        ]);

        if (Circle::where('zone_id', $validated['zone_id'])
                  ->where('name', $validated['name'])
                  ->where('id', '!=', $circle->id)
                  ->exists()) {
             return back()->withErrors(['name' => 'The circle name has already been taken in this zone.'])->withInput();
        }

        $circle->update($validated);

        return redirect()->route('admin.circles.index')
            ->with('success', 'Circle updated successfully.');
    }

    public function destroy(Circle $circle)
    {
        $circle->delete();

        return redirect()->route('admin.circles.index')
            ->with('success', 'Circle deleted successfully.');
    }
}
