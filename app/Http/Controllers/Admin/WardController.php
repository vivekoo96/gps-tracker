<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ward;
use App\Models\Circle;
use Illuminate\Http\Request;

class WardController extends Controller
{
    public function index()
    {
        $wards = Ward::with(['circle.zone'])->withCount(['transferStations', 'devices'])->paginate(10);
        return view('admin.wards.index', compact('wards'));
    }

    public function create()
    {
        $circles = Circle::with('zone')->get()->sortBy('zone.name');
        return view('admin.wards.create', compact('circles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'circle_id' => 'required|exists:circles,id',
            'description' => 'nullable|string',
        ]);

        if (Ward::where('circle_id', $validated['circle_id'])->where('name', $validated['name'])->exists()) {
             return back()->withErrors(['name' => 'The ward name has already been taken in this circle.'])->withInput();
        }

        Ward::create($validated);

        return redirect()->route('admin.wards.index')
            ->with('success', 'Ward created successfully.');
    }

    public function show(Ward $ward)
    {
        return view('admin.wards.show', compact('ward'));
    }

    public function edit(Ward $ward)
    {
        $circles = Circle::with('zone')->get()->sortBy('zone.name');
        return view('admin.wards.edit', compact('ward', 'circles'));
    }

    public function update(Request $request, Ward $ward)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'circle_id' => 'required|exists:circles,id',
            'description' => 'nullable|string',
        ]);

        if (Ward::where('circle_id', $validated['circle_id'])
                  ->where('name', $validated['name'])
                  ->where('id', '!=', $ward->id)
                  ->exists()) {
             return back()->withErrors(['name' => 'The ward name has already been taken in this circle.'])->withInput();
        }

        $ward->update($validated);

        return redirect()->route('admin.wards.index')
            ->with('success', 'Ward updated successfully.');
    }

    public function destroy(Ward $ward)
    {
        $ward->delete();

        return redirect()->route('admin.wards.index')
            ->with('success', 'Ward deleted successfully.');
    }
}
