<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Landmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandmarkController extends Controller
{
    public function index()
    {
        $landmarks = Landmark::latest()->paginate(10);
        return view('admin.landmarks.index', compact('landmarks'));
    }

    public function create()
    {
        return view('admin.landmarks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:General,Garage,Dump Yard,Transfer Station,Office,Other',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        Landmark::create([
            ...$validated,
            'created_by' => Auth::id()
        ]);

        return redirect()->route('admin.landmarks.index')
            ->with('success', 'Landmark created successfully.');
    }

    public function edit(Landmark $landmark)
    {
        return view('admin.landmarks.edit', compact('landmark'));
    }

    public function update(Request $request, Landmark $landmark)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:General,Garage,Dump Yard,Transfer Station,Office,Other',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $landmark->update($validated);

        return redirect()->route('admin.landmarks.index')
            ->with('success', 'Landmark updated successfully.');
    }

    public function destroy(Landmark $landmark)
    {
        $landmark->delete();
        return redirect()->route('admin.landmarks.index')
            ->with('success', 'Landmark deleted successfully.');
    }
}
