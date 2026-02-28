<?php

namespace App\Http\Controllers;

use App\Models\MpesaPayment;
use App\Models\Package;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Iankumu\Mpesa\Facades\Mpesa;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    // Show pricing page with packages
    public function index()
    {
        $packages = Package::active()->get();

        $activeSubscription = Auth::user()
            ->activeSubscription()
            ->with('package')
            ->first();

        return Inertia::render('Subscriptions/Index', [
            'packages'           => $packages,
            'activeSubscription' => $activeSubscription,
        ]);
    }

    // Initiate STK Push
    public function subscribe(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'phone'      => ['required', 'regex:/^(?:\+?254|0)[7-9][0-9]{8}$/'],
        ]);

        $package = Package::findOrFail($request->package_id);
        $user    = Auth::user();

        // Normalize phone to 254 format
        $phone = preg_replace('/^0/', '254', $request->phone);
        $phone = preg_replace('/^\+/', '', $phone);

        // Create pending payment record
        $payment = MpesaPayment::create([
            'user_id'           => $user->id,
            'package_id'        => $package->id,
            'phone_number'      => $phone,
            'amount'            => $package->price,
            'account_reference' => $package->name,
            'status'            => 'pending',
        ]);

        // Create pending subscription
        $subscription = Subscription::create([
            'user_id'    => $user->id,
            'package_id' => $package->id,
            'status'     => 'pending',
        ]);

        $payment->update(['subscription_id' => $subscription->id]);

        try {
            $response = Mpesa::stkpush(
                $phone,
                $package->price,
                $package->name,
                'Subscription'
            );

            $result = json_decode($response, true);

            Log::info('STK Push Response', $result);

            if (isset($result['CheckoutRequestID'])) {
                $payment->update([
                    'merchant_request_id' => $result['MerchantRequestID'] ?? null,
                    'checkout_request_id' => $result['CheckoutRequestID'],
                ]);

                return back()->with('success', 'STK Push sent to ' . $request->phone . '. Enter your M-Pesa PIN to complete payment.');
            }

            // STK push failed
            $payment->update(['status' => 'failed']);
            $subscription->update(['status' => 'cancelled']);

            return back()->with('error', 'Failed to initiate payment. Please try again.');

        } catch (\Exception $e) {
            Log::error('STK Push Error: ' . $e->getMessage());
            $payment->update(['status' => 'failed']);
            $subscription->update(['status' => 'cancelled']);

            return back()->with('error', 'Payment error: ' . $e->getMessage());
        }
    }

    // Cancel subscription
    public function cancel(Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        $subscription->update(['status' => 'cancelled']);

        return back()->with('success', 'Subscription cancelled.');
    }
}