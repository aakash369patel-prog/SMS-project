<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentFeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $amount = (float) $this->amount;
        $discount = (float) ($this->discount_amount ?? 0);
        $effective = max(0, $amount - $discount);
        $paid = (float) $this->amount_paid;

        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'fee_type_id' => $this->fee_type_id,
            'title' => $this->title,
            'amount' => $amount,
            'discount_amount' => $discount,
            'discount_type' => $this->discount_type,
            'discount_note' => $this->discount_note,
            'effective_amount' => $effective,
            'amount_paid' => $paid,
            'balance' => max(0, round($effective - $paid, 2)),
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status,
            'academic_year' => $this->academic_year,
            'student' => StudentResource::make($this->whenLoaded('student')),
            'fee_type' => FeeTypeResource::make($this->whenLoaded('feeType')),
            'payments' => FeePaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
