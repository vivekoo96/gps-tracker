<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index()
    {
        $routes = Route::latest()->paginate(10);
        return view('admin.routes.index', compact('routes'));
    }

    public function create()
    {
        return view('admin.routes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stops' => 'nullable|json', // Expecting JSON string from UI or generic text
        ]);

        Route::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'stops' => $validated['stops'] ? json_decode($validated['stops'], true) : null,
        ]);

        return redirect()->route('admin.routes.index')
            ->with('success', 'Route created successfully.');
    }

    public function edit(Route $route)
    {
        return view('admin.routes.edit', compact('route'));
    }

    public function update(Request $request, Route $route)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stops' => 'nullable|json',
        ]);

        $route->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'stops' => $validated['stops'] ? json_decode($validated['stops'], true) : null,
        ]);

        return redirect()->route('admin.routes.index')
            ->with('success', 'Route updated successfully.');
    }

    public function destroy(Route $route)
    {
        $route->delete();
        return redirect()->route('admin.routes.index')
            ->with('success', 'Route deleted successfully.');
    }
}
