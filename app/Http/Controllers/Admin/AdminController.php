<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::whereNotNull('email_verified_at')->count(),
            'total_roles' => Role::count(),
            'recent_users' => User::latest()->take(5)->get(),
            'user_registrations_this_month' => User::whereMonth('created_at', now()->month)->count(),
            'user_registrations_last_month' => User::whereMonth('created_at', now()->subMonth()->month)->count(),
        ];

        // Calculate growth percentage
        $stats['user_growth_percentage'] = $stats['user_registrations_last_month'] > 0 
            ? round((($stats['user_registrations_this_month'] - $stats['user_registrations_last_month']) / $stats['user_registrations_last_month']) * 100, 1)
            : 0;

        return view('admin.dashboard', compact('stats'));
    }
}


