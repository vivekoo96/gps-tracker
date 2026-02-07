<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $delivery;

    /**
     * Create a new job instance.
     */
    public function __construct(WebhookDelivery $delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $webhook = $this->delivery->webhook;

        if (!$webhook || !$webhook->active) {
            $this->delivery->update(['status' => 'cancelled']);
            return;
        }

        $this->delivery->increment('attempt_count');

        try {
            $payload = $this->delivery->payload;
            $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret);

            $response = Http::timeout(10)
                ->withHeaders([
                    'X-GPS-Signature' => $signature,
                    'X-GPS-Event' => $this->delivery->event_type,
                    'Content-Type' => 'application/json',
                ])
                ->post($webhook->url, $payload);

            $this->delivery->update([
                'http_status_code' => $response->status(),
                'response_body' => mb_substr($response->body(), 0, 1000), // Truncate long bodies
            ]);

            if ($response->successful()) {
                $this->delivery->update([
                    'status' => 'success',
                    'delivered_at' => now(),
                ]);
            } else {
                $this->handleFailure();
            }

        } catch (\Exception $e) {
            Log::error('Webhook Delivery Failed', [
                'delivery_id' => $this->delivery->id,
                'error' => $e->getMessage()
            ]);

            $this->delivery->update([
                'status' => 'failed',
                'response_body' => mb_substr($e->getMessage(), 0, 1000),
            ]);

            $this->handleFailure();
        }
    }

    /**
     * Handle delivery failure and scheduling retries.
     */
    protected function handleFailure(): void
    {
        $webhook = $this->delivery->webhook;

        if ($this->delivery->attempt_count < $webhook->max_retry_attempts) {
            $delayInMinutes = pow(2, $this->delivery->attempt_count); // 2, 4, 8 minutes
            
            $this->delivery->update([
                'status' => 'retrying',
                'next_retry_at' => now()->addMinutes($delayInMinutes),
            ]);

            self::dispatch($this->delivery)->delay(now()->addMinutes($delayInMinutes));
        } else {
            $this->delivery->update(['status' => 'failed']);
        }
    }
}
