<?php

namespace App\Http\Requests;

use App\Models\Exam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:30', 'unique:exams,code'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'exam_type' => ['required', Rule::in(Exam::TYPES)],
            'exam_date' => ['required', 'date'],
            'max_marks' => ['required', 'numeric', 'min:1', 'max:1000'],
            'academic_year' => ['required', 'string', 'max:9'],
            'description' => ['nullable', 'string'],
        ];
    }
}
