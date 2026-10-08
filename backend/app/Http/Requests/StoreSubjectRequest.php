<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
        ];
    }
}
