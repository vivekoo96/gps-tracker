<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateV2Api
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key');
        $secret = $request->header('X-API-Secret');

        if (!$key || !$secret) {
            return response()->json([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'API Key and Secret are required in headers.'
                ]
            ], 401);
        }

        $apiKey = ApiKey::where('key', $key)
            ->where('secret', $secret)
            ->first();

        if (!$apiKey || !$apiKey->isValid()) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'The provided API credentials are invalid or expired.'
                ]
            ], 401);
        }

        // Authenticate the user for the request session
        auth()->login($apiKey->user);

        // Update last used
        $apiKey->update(['last_used_at' => now()]);

        // Attach API key to request for logging
        $request->attributes->set('api_key_id', $apiKey->id);
        $request->attributes->set('start_time', microtime(true));

        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        $apiKeyId = $request->attributes->get('api_key_id');
        $startTime = $request->attributes->get('start_time');

        if ($apiKeyId && $startTime) {
            \App\Models\ApiRequest::create([
                'api_key_id' => $apiKeyId,
                'endpoint' => $request->fullUrl(),
                'method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'response_time_ms' => round((microtime(true) - $startTime) * 1000),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
    }
}
