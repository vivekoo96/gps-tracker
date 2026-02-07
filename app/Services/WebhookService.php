<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Jobs\ProcessWebhookJob;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Dispatch a webhook event.
     */
    public function trigger(string $eventType, array $data, ?int $vendorId = null): void
    {
        // Find all active webhooks subscribed to this event
        $webhooks = Webhook::where('active', true)
            ->whereJsonContains('events', $eventType)
            ->when($vendorId, function ($query) use ($vendorId) {
                return $query->where('vendor_id', $vendorId);
            })
            ->get();

        foreach ($webhooks as $webhook) {
            try {
                // Create delivery record
                $delivery = WebhookDelivery::create([
                    'webhook_id' => $webhook->id,
                    'event_type' => $eventType,
                    'payload' => [
                        'id' => uniqid('evt_'),
                        'type' => $eventType,
                        'created_at' => now()->toIso8601String(),
                        'data' => $data,
                    ],
                    'status' => 'pending',
                ]);

                // Dispatch job
                ProcessWebhookJob::dispatch($delivery);

            } catch (\Exception $e) {
                Log::error('Failed to trigger webhook', [
                    'webhook_id' => $webhook->id,
                    'event' => $eventType,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
