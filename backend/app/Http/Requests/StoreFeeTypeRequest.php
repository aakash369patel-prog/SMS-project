<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeeTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:30', 'unique:fee_types,code'],
            'amount' => ['required', 'numeric', 'min:0'],
            'academic_year' => ['required', 'string', 'max:9'],
            'description' => ['nullable', 'string'],
        ];
    }
}
