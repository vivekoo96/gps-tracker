<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\Vendor;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $vendor = auth()->user()->vendor;
        $currentPlan = $vendor->subscriptionPlan;
        $availablePlans = SubscriptionPlan::where('is_active', true)->get();
        $transactions = $vendor->transactions()->latest()->paginate(5);

        return view('vendor.subscription.index', compact('vendor', 'currentPlan', 'availablePlans', 'transactions'));
    }

    public function upgrade(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id'
        ]);

        $vendor = auth()->user()->vendor;
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        try {
            $order = $this->paymentService->createOrder($vendor, $plan);
            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to initiate payment'], 500);
        }
    }

    public function verify(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature' => 'required'
        ]);

        $transaction = $this->paymentService->verifyPayment($request->all());

        if ($transaction) {
            // Update Vendor Subscription
            $vendor = auth()->user()->vendor;
            $newPlan = $transaction->plan;

            $vendor->update([
                'subscription_plan_id' => $newPlan->id,
                'subscription_expires_at' => now()->addDays($newPlan->duration_days),
                'status' => 'active'
            ]);

            return response()->json(['status' => 'success', 'message' => 'Subscription updated successfully!']);
        }

        return response()->json(['status' => 'error', 'message' => 'Payment verification failed'], 400);
    }
}
