<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'fee_type_id' => ['required', 'exists:fee_types,id'],
            'title' => ['nullable', 'string', 'max:150'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'academic_year' => ['nullable', 'string', 'max:9'],
        ];
    }
}
