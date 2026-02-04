<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()->latest('id')->paginate(10);

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = \Spatie\Permission\Models\Permission::all();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:roles,name'],
            'permissions' => ['array'],
        ]);

        $role = Role::create(['name' => $validated['name']]);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        return redirect()->route('admin.roles.index')->with('status', 'Role created successfully');
    }

    public function edit(Role $role): View
    {
        $permissions = \Spatie\Permission\Models\Permission::all();
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['array'],
        ]);

        $role->update(['name' => $validated['name']]);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        return redirect()->route('admin.roles.index')->with('status', 'Role updated successfully');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'super_admin') {
            return back()->with('error', 'Cannot delete Super Admin role');
        }
        
        $role->delete();
        return redirect()->route('admin.roles.index')->with('status', 'Role deleted');
    }
}


