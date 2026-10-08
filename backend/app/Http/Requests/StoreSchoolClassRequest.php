<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'section' => ['nullable', 'string', 'max:10'],
            'academic_year' => ['required', 'string', 'max:9'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:200'],
            'class_teacher_id' => ['nullable', 'exists:teachers,id'],
        ];
    }
}
