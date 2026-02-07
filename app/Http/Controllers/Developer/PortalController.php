<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PortalController extends Controller
{
    /**
     * Show the developer portal dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $apiKeys = ApiKey::where('user_id', $user->id)->get();
        $webhooks = Webhook::where('vendor_id', $user->vendor_id)->get();
        
        return view('developer.portal.index', compact('apiKeys', 'webhooks'));
    }

    /**
     * Generate a new API Key.
     */
    public function generateKey(Request $request)
    {
        $request->validate(['name' => 'required|string|max:50']);
        
        $user = auth()->user();
        $apiKey = ApiKey::generate($user->id, $request->name, $user->vendor_id);

        return back()->with('success', "API Key '{$request->name}' generated successfully. Secret is: " . $apiKey->secret . " (Save it now!)");
    }

    /**
     * Revoke an API Key.
     */
    public function revokeKey($id)
    {
        $apiKey = ApiKey::where('user_id', auth()->id())->findOrFail($id);
        $apiKey->delete();

        return back()->with('success', 'API Key revoked successfully.');
    }

    /**
     * Store a new Webhook.
     */
    public function storeWebhook(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'events' => 'required|array',
        ]);

        Webhook::create([
            'vendor_id' => auth()->user()->vendor_id,
            'url' => $request->url,
            'events' => $request->events,
            'secret' => Str::random(32),
            'active' => true,
        ]);

        return back()->with('success', 'Webhook endpoint added successfully.');
    }

    /**
     * Show webhook delivery logs.
     */
    public function webhookLogs($id)
    {
        $webhook = Webhook::where('vendor_id', auth()->user()->vendor_id)->findOrFail($id);
        $deliveries = WebhookDelivery::where('webhook_id', $webhook->id)
            ->latest()
            ->paginate(20);

        return view('developer.portal.webhook-logs', compact('webhook', 'deliveries'));
    }

    /**
     * Documentation page.
     */
    public function documentation()
    {
        return view('developer.portal.docs');
    }

    /**
     * Download SDK files.
     */
    public function downloadSdk($type)
    {
        $files = [
            'php' => base_path('sdks/php/GpsClient.php'),
            'javascript' => base_path('sdks/javascript/GpsClient.js'),
            'python' => base_path('sdks/python/gps_client.py'),
        ];

        if (!isset($files[$type]) || !file_exists($files[$type])) {
            abort(404, 'SDK file not found.');
        }

        return response()->download($files[$type]);
    }
}
