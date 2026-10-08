<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeePaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_fee_id' => $this->student_fee_id,
            'amount' => (float) $this->amount,
            'payment_date' => $this->payment_date?->toDateString(),
            'payment_method' => $this->payment_method,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
