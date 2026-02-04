<?php

namespace App\Services;

use Razorpay\Api\Api;
use App\Models\Vendor;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $api;

    public function __construct()
    {
        $key = site_setting('razorpay_key', config('razorpay.key'));
        $secret = site_setting('razorpay_secret', config('razorpay.secret'));
        
        $this->api = new Api($key, $secret);
    }

    public function createOrder(Vendor $vendor, SubscriptionPlan $plan)
    {
        try {
            $orderData = [
                'receipt'         => 'rcpt_' . $vendor->id . '_' . time(),
                'amount'          => $plan->price * 100, // Amount in paise
                'currency'        => 'INR',
                'notes'           => [
                    'vendor_id' => $vendor->id,
                    'plan_id' => $plan->id,
                ]
            ];

            $razorpayOrder = $this->api->order->create($orderData);

            // Create initial transaction record
            $transaction = Transaction::create([
                'vendor_id' => $vendor->id,
                'subscription_plan_id' => $plan->id,
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount' => $plan->price,
                'currency' => 'INR',
                'status' => 'created',
                'receipt' => $orderData['receipt']
            ]);

            return [
                'order_id' => $razorpayOrder['id'],
                'amount' => $plan->price * 100,
                'currency' => 'INR',
                'key' => site_setting('razorpay_key', config('razorpay.key')),
                'transaction' => $transaction
            ];

        } catch (\Exception $e) {
            Log::error('Razorpay Order Creation Failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function verifyPayment($attributes)
    {
        try {
            $this->api->utility->verifyPaymentSignature($attributes);
            
            $transaction = Transaction::where('razorpay_order_id', $attributes['razorpay_order_id'])->firstOrFail();
            
            $transaction->update([
                'razorpay_payment_id' => $attributes['razorpay_payment_id'],
                'status' => 'paid',
                'method' => 'razorpay',
                'response_data' => json_encode($attributes)
            ]);

            return $transaction;

        } catch (\Exception $e) {
            Log::error('Razorpay Payment Verification Failed: ' . $e->getMessage());
            
            // Mark as failed if we can find the transaction
            if (isset($attributes['razorpay_order_id'])) {
                Transaction::where('razorpay_order_id', $attributes['razorpay_order_id'])
                    ->update(['status' => 'failed']);
            }
            
            return false;
        }
    }
}
