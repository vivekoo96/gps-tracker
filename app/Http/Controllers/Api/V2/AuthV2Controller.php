<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\ApiKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthV2Controller extends BaseV2Controller
{
    /**
     * Login user and return API credentials.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Invalid credentials', 'UNAUTHORIZED', 401);
        }

        $user = Auth::user();
        
        // Find existing API key or generate one
        $apiKey = ApiKey::where('user_id', $user->id)->first();
        
        if (!$apiKey) {
            $apiKey = ApiKey::generate($user->id, 'Auto-generated for ' . $user->name, $user->vendor_id);
        }

        // We need to show the secret once during login
        // If it's an existing key, we might need to regenerate if secret is hashed/hidden
        // But in this implementation, we store it plain in database (per ApiKey model)
        // and hide it in serialization.
        
        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'api_credentials' => [
                'key' => $apiKey->key,
                'secret' => $apiKey->secret, // Secret is plain-text in DB currently
            ],
            'vendor' => $user->vendor ? [
                'id' => $user->vendor->id,
                'name' => $user->vendor->name,
                'subdomain' => $user->vendor->subdomain,
            ] : null,
        ]);
    }

    /**
     * Get authenticated user info.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success([
            'user' => auth()->user(),
            'api_key_id' => $request->attributes->get('api_key_id')
        ]);
    }
}
