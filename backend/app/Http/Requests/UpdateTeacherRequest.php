<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacherId = $this->route('teacher')?->id;

        return [
            'employee_code' => ['sometimes', 'string', 'max:50', Rule::unique('teachers', 'employee_code')->ignore($teacherId)],
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('teachers', 'email')->ignore($teacherId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'department' => ['nullable', 'string', 'max:100'],
            'hire_date' => ['nullable', 'date'],
        ];
    }
}
