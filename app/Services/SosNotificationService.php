<?php

namespace App\Services;

use App\Models\SosAlert;
use App\Models\EmergencyContact;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SosNotificationService
{
    /**
     * Send all notifications for an SOS alert
     */
    public function sendNotifications(SosAlert $alert): void
    {
        $contacts = EmergencyContact::where('device_id', $alert->device_id)
            ->orderBy('priority', 'asc')
            ->get();

        if ($contacts->isEmpty()) {
            Log::warning("No emergency contacts found for device {$alert->device_id}");
            return;
        }

        $message = $this->buildMessage($alert);

        foreach ($contacts as $contact) {
            if ($contact->notify_sms && $contact->phone) {
                $this->sendSMS($contact->formatted_phone, $message);
            }

            if ($contact->notify_email && $contact->email) {
                $this->sendEmail($contact->email, $contact->name, $alert, $message);
            }
        }
    }

    /**
     * Build SOS alert message
     */
    protected function buildMessage(SosAlert $alert): string
    {
        $device = $alert->device;
        $deviceName = $device->vehicle_no ?? $device->name ?? 'Unknown Vehicle';
        
        $location = $alert->latitude && $alert->longitude
            ? "Location: https://maps.google.com/?q={$alert->latitude},{$alert->longitude}"
            : "Location: Not available";

        return "🚨 SOS ALERT!\n"
            . "Vehicle: {$deviceName}\n"
            . "Time: {$alert->triggered_at->format('d-M-Y h:i A')}\n"
            . "Speed: {$alert->speed} km/h\n"
            . $location;
    }

    /**
     * Send SMS via MSG91
     */
    protected function sendSMS(string $phone, string $message): void
    {
        try {
            $authKey = config('services.msg91.auth_key');
            $senderId = config('services.msg91.sender_id', 'SOSEMC');

            if (!$authKey) {
                Log::error('MSG91 auth key not configured');
                return;
            }

            $response = Http::post('https://api.msg91.com/api/v5/flow/', [
                'authkey' => $authKey,
                'mobiles' => $phone,
                'message' => $message,
                'sender' => $senderId,
                'route' => '4', // Transactional route
                'country' => '91'
            ]);

            if ($response->successful()) {
                Log::info("SMS sent to {$phone}");
            } else {
                Log::error("Failed to send SMS to {$phone}: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("SMS sending error: " . $e->getMessage());
        }
    }

    /**
     * Send email notification
     */
    protected function sendEmail(string $email, string $name, SosAlert $alert, string $message): void
    {
        try {
            Mail::send('emails.sos-alert', [
                'name' => $name,
                'alert' => $alert,
                'message' => $message
            ], function ($mail) use ($email, $alert) {
                $mail->to($email)
                    ->subject('🚨 SOS Emergency Alert - ' . ($alert->device->vehicle_no ?? $alert->device->name));
            });

            Log::info("Email sent to {$email}");
        } catch (\Exception $e) {
            Log::error("Email sending error: " . $e->getMessage());
        }
    }
}
