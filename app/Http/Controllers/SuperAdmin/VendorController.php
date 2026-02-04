<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Vendor;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::with('subscriptionPlan')->latest()->paginate(10);
        return view('super-admin.vendors.index', compact('vendors'));
    }

    public function create()
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();
        return view('super-admin.vendors.create', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'subdomain' => 'nullable|string|alpha_dash|max:50|unique:vendors,subdomain',
            'email' => 'required|email|unique:vendors,email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'primary_color' => 'nullable|string|max:7',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        DB::transaction(function () use ($validated) {
            // 1. Create Vendor (Tenant Admin)
            $vendor = Vendor::create($validated);

            // 2. Create Admin User
            $user = \App\Models\User::create([
                'name' => $validated['company_name'] . ' Admin',
                'email' => $validated['email'],
                'password' => bcrypt('password'), // Default password
                'email_verified_at' => now(),
                'role' => 'vendor_admin',
                'vendor_id' => $vendor->id,
            ]);
            
            // Assign Spatie Role
            $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'vendor_admin']);
            $user->assignRole($role);
        });

        return redirect()->route('super_admin.vendors.index')
            ->with('success', 'Admin and User created successfully');
    }

    public function edit($id)
    {
        $vendor = Vendor::findOrFail($id);
        $plans = SubscriptionPlan::where('is_active', true)->get();
        return view('super-admin.vendors.edit', compact('vendor', 'plans'));
    }

    public function update(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'subdomain' => 'nullable|string|alpha_dash|max:50|unique:vendors,subdomain,' . $id,
            'email' => 'required|email|unique:vendors,email,' . $id . ',id',
            'phone' => 'nullable|string|max:20',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'status' => 'required|in:active,inactive,suspended',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'primary_color' => 'nullable|string|max:7',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $vendor->update($validated);

        return redirect()->route('super_admin.vendors.index')
            ->with('success', 'Admin details updated successfully');
    }

    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);
        
        // Delete associated admin user if exists
        $user = \App\Models\User::where('vendor_id', $vendor->id)->where('role', 'vendor_admin')->first();
        if ($user) {
            $user->delete();
        }

        $vendor->delete();

        return redirect()->route('super_admin.vendors.index')
            ->with('success', 'Admin deleted successfully');
    }
}
