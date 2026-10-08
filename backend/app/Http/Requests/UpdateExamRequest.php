<?php

namespace App\Http\Requests;

use App\Models\Exam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $exam = $this->route('exam');

        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'code' => ['sometimes', 'string', 'max:30', Rule::unique('exams', 'code')->ignore($exam?->id)],
            'school_class_id' => ['sometimes', 'exists:school_classes,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'exam_type' => ['sometimes', Rule::in(Exam::TYPES)],
            'exam_date' => ['sometimes', 'date'],
            'max_marks' => ['sometimes', 'numeric', 'min:1', 'max:1000'],
            'academic_year' => ['sometimes', 'string', 'max:9'],
            'description' => ['nullable', 'string'],
        ];
    }
}
