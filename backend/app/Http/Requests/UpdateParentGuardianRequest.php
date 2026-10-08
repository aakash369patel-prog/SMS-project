<?php

namespace App\Http\Requests;

use App\Models\ParentGuardian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateParentGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var ParentGuardian|null $parent */
        $parent = $this->route('parent');

        return [
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('parents', 'email')->ignore($parent?->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
            'students' => ['nullable', 'array'],
            'students.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'students.*.relation' => ['required', Rule::in(['father', 'mother', 'guardian', 'other'])],
        ];
    }
}
