<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeeTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $feeType = $this->route('fee_type');

        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'code' => ['sometimes', 'string', 'max:30', Rule::unique('fee_types', 'code')->ignore($feeType?->id)],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'academic_year' => ['sometimes', 'string', 'max:9'],
            'description' => ['nullable', 'string'],
        ];
    }
}
