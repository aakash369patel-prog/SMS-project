<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subject = $this->route('subject');

        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'code' => ['sometimes', 'string', 'max:20', Rule::unique('subjects', 'code')->ignore($subject?->id)],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
        ];
    }
}
