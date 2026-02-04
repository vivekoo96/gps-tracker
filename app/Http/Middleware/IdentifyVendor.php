<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Vendor;

class IdentifyVendor {
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        
        // If we have a subdomain (e.g., ghmc.gps-tracker.com)
        if (count($parts) >= 3) {
            $subdomain = $parts[0];
            
            // Skip common subdomains like 'www'
            if ($subdomain !== 'www') {
                $vendor = Vendor::where('subdomain', $subdomain)->first();
                if ($vendor) {
                    app()->instance('current_vendor', $vendor);
                }
            }
        }

        return $next($request);
    }
}
