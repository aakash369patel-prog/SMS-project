<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'section' => ['nullable', 'string', 'max:10'],
            'academic_year' => ['sometimes', 'string', 'max:9'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:200'],
            'class_teacher_id' => ['nullable', 'exists:teachers,id'],
        ];
    }
}
