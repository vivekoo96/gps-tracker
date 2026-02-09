<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\User;
use App\Models\ApiKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserV2Controller extends BaseV2Controller
{
    /**
     * Display a listing of users in the tenant.
     */
    public function index(Request $request): JsonResponse
    {
        // VendorScope automatically filters by auth()->user()->vendor_id
        $query = User::query();
        
        return $this->paginate($request, $query);
    }

    /**
     * Create a new sub-user under the current admin's tenant.
     */
    public function store(Request $request): JsonResponse
    {
        $admin = auth()->user();

        // Security check: Only Admins can create users
        if (!$admin->isSuperAdmin() && !$admin->isVendorAdmin()) {
            return $this->error('Only administrators can create users.', 'UNAUTHORIZED_ACTION', 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'nullable|string|in:user,vendor_admin',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'user',
        ]);

        // Automatically generate an API key for the new user
        $apiKey = ApiKey::generate($user->id, 'Default Key for ' . $user->name, $user->vendor_id);

        return $this->success([
            'user' => $user,
            'api_credentials' => [
                'key' => $apiKey->key,
                'secret' => $apiKey->secret,
            ]
        ], 'User created successfully.', 201);
    }

    /**
     * Display the specified user.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('User not found.', 'NOT_FOUND', 404);
        }

        return $this->success($user);
    }
}
