<?php

namespace App\Http\Requests;

use App\Models\FeePayment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordFeePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(FeePayment::METHODS)],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
