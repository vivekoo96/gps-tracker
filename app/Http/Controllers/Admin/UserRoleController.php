<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles')->paginate(10);
        $roles = Role::all();
        return view('admin.users.roles', compact('users', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'roles' => ['array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user->syncRoles($request->input('roles', []));

        return back()->with('status', 'User roles updated');
    }
}


