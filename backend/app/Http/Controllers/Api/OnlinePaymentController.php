<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OnlinePaymentOrder;
use App\Models\StudentFee;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

class OnlinePaymentController extends Controller
{
    public function index()
    {
        return response()->json(
            OnlinePaymentOrder::query()
                ->with(['studentFee.student', 'user'])
                ->latest()
                ->paginate(25)
        );
    }

    public function createIntent(Request $request, StudentFee $studentFee, PaymentGatewayService $gateway)
    {
        $this->assertParentOwnsFee($request, $studentFee);

        $data = $request->validate([
            'provider' => ['required', 'in:razorpay,stripe,simulated'],
        ]);

        try {
            $order = $gateway->createOrder($studentFee, $request->user(), $data['provider']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $order->load('studentFee')], 201);
    }

    public function confirm(Request $request, OnlinePaymentOrder $onlinePaymentOrder, PaymentGatewayService $gateway)
    {
        if ($request->user()->isParent()) {
            $this->assertParentOwnsFee($request, $onlinePaymentOrder->studentFee);
        }

        if ($onlinePaymentOrder->user_id && $onlinePaymentOrder->user_id !== $request->user()->id && ! $request->user()->isStaffMember()) {
            abort(403);
        }

        $data = $request->validate([
            'external_payment_id' => ['nullable', 'string', 'max:100'],
        ]);

        $order = $gateway->confirmOrder($onlinePaymentOrder, $data['external_payment_id'] ?? null);

        return response()->json(['data' => $order]);
    }

    private function assertParentOwnsFee(Request $request, StudentFee $studentFee): void
    {
        $profile = $request->user()->parentProfile;
        if (! $profile) {
            abort(403);
        }

        $studentIds = $profile->students()->pluck('id');
        if (! $studentIds->contains($studentFee->student_id)) {
            abort(403);
        }
    }
}
