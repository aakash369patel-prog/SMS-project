<?php

namespace App\Services;

use App\Models\FeePayment;
use App\Models\OnlinePaymentOrder;
use App\Models\StudentFee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentGatewayService
{
    public function createOrder(StudentFee $studentFee, User $user, string $provider): OnlinePaymentOrder
    {
        $provider = strtolower($provider);
        if (! in_array($provider, ['razorpay', 'stripe', 'simulated'], true)) {
            $provider = 'simulated';
        }

        $balance = $studentFee->balanceDue();
        if ($balance <= 0) {
            throw new \InvalidArgumentException('Nothing due on this invoice.');
        }

        $orderId = strtoupper($provider).'_'.Str::upper(Str::random(12));

        return OnlinePaymentOrder::query()->create([
            'student_fee_id' => $studentFee->id,
            'user_id' => $user->id,
            'provider' => $provider,
            'amount' => $balance,
            'currency' => config('services.payment.currency', 'INR'),
            'status' => 'created',
            'external_order_id' => $orderId,
            'meta' => [
                'demo_mode' => $this->isDemoMode($provider),
                'checkout_hint' => $this->checkoutHint($provider),
            ],
        ]);
    }

    public function confirmOrder(OnlinePaymentOrder $order, ?string $externalPaymentId = null): OnlinePaymentOrder
    {
        if ($order->status === 'paid') {
            return $order;
        }

        DB::transaction(function () use ($order, $externalPaymentId): void {
            $order->update([
                'status' => 'paid',
                'external_payment_id' => $externalPaymentId ?? ('pay_'.Str::lower(Str::random(14))),
                'paid_at' => now(),
            ]);

            $fee = $order->studentFee;
            FeePayment::query()->create([
                'student_fee_id' => $fee->id,
                'amount' => $order->amount,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'online',
                'reference' => $order->provider.':'.$order->external_payment_id,
                'notes' => 'Online payment via '.$order->provider,
                'recorded_by' => $order->user_id,
            ]);

            $fee->refreshPaymentTotals();
        });

        return $order->fresh(['studentFee']);
    }

    private function isDemoMode(string $provider): bool
    {
        if ($provider === 'simulated') {
            return true;
        }
        if ($provider === 'razorpay') {
            return empty(config('services.razorpay.key'));
        }
        if ($provider === 'stripe') {
            return empty(config('services.stripe.secret'));
        }

        return true;
    }

    private function checkoutHint(string $provider): string
    {
        return match ($provider) {
            'razorpay' => 'In production, load Razorpay Checkout with order id from API.',
            'stripe' => 'In production, use PaymentIntent client_secret with Stripe.js.',
            default => 'Demo: call confirm to complete payment without a real gateway.',
        };
    }
}
