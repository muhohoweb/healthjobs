<?php

namespace App\Http\Controllers;

use App\Models\MpesaPayment;
use App\Models\Subscription;
use App\Models\Package;
use Iankumu\Mpesa\Facades\Mpesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class MpesaController extends Controller
{
    // STK Push — called from SubscriptionController
    public static function stkPush(string $phone, float $amount, string $accountReference): array
    {
        $response = Mpesa::stkpush($phone, $amount, $accountReference, 'Subscription');
        return json_decode($response, true) ?? [];
    }

    // M-Pesa callback from Safaricom
    public function callback(Request $request)
    {
        Log::info('=== MPESA SUBSCRIPTION CALLBACK ===');
        Log::info('Raw content: ' . $request->getContent());

        $callback = $request->input('Body.stkCallback');

        if (!$callback) {
            Log::warning('Invalid callback - no stkCallback found');
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid']);
        }

        $checkoutRequestId = $callback['CheckoutRequestID'];
        $resultCode        = $callback['ResultCode'];
        $resultDesc        = $callback['ResultDesc'];

        Log::info('Callback details', [
            'checkout_request_id' => $checkoutRequestId,
            'result_code'         => $resultCode,
            'result_desc'         => $resultDesc,
        ]);

        $payment = MpesaPayment::where('checkout_request_id', $checkoutRequestId)->first();

        if (!$payment) {
            Log::warning('Payment not found: ' . $checkoutRequestId);
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        // Extract metadata
        $metadata = [];
        foreach ($callback['CallbackMetadata']['Item'] ?? [] as $item) {
            $metadata[$item['Name']] = $item['Value'] ?? null;
        }

        Log::info('Callback metadata', $metadata);

        $payment->update([
            'result_code'          => $resultCode,
            'result_desc'          => $resultDesc,
            'mpesa_receipt_number' => $metadata['MpesaReceiptNumber'] ?? null,
            'amount'               => $metadata['Amount'] ?? $payment->amount,
            'phone_number'         => $metadata['PhoneNumber'] ?? $payment->phone_number,
            'status'               => $resultCode == 0 ? 'completed' : 'failed',
            'callback_data'        => $callback,
            'paid_at'              => $resultCode == 0 ? now() : null,
        ]);

        if ($resultCode == 0) {
            // Activate subscription
            $subscription = Subscription::find($payment->subscription_id);
            $package      = Package::find($payment->package_id);

            if ($subscription && $package) {
                $subscription->update([
                    'status'          => 'active',
                    'starts_at'       => now(),
                    'expires_at'      => Subscription::calculateExpiry($package->billing_cycle),
                    'mpesa_reference' => $metadata['MpesaReceiptNumber'] ?? null,
                    'amount_paid'     => $metadata['Amount'] ?? $payment->amount,
                ]);

                Log::info('Subscription activated', [
                    'user_id'    => $subscription->user_id,
                    'package'    => $package->name,
                    'expires_at' => $subscription->expires_at,
                ]);
            }
        } else {
            // Cancel subscription on failed payment
            Subscription::find($payment->subscription_id)
                ?->update(['status' => 'cancelled']);

            Log::info('Payment failed — subscription cancelled', [
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
            ]);
        }

        Log::info('=== MPESA CALLBACK COMPLETE ===');

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    // Check payment status by checkout_request_id
    public function checkStatus(string $checkoutRequestId)
    {
        $payment = MpesaPayment::where('checkout_request_id', $checkoutRequestId)
            ->with('subscription')
            ->first();

        if (!$payment) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'status'          => $payment->status,
            'receipt'         => $payment->mpesa_receipt_number,
            'amount'          => $payment->amount,
            'subscription'    => $payment->subscription?->status,
            'expires_at'      => $payment->subscription?->expires_at,
        ]);
    }

    // Admin — view all payments
    public function index()
    {
        $payments = MpesaPayment::with(['user', 'package', 'subscription'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
        ]);
    }
}