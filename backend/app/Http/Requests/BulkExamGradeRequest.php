<?php

namespace App\Http\Requests;

use App\Models\Exam;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BulkExamGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'records.*.marks_obtained' => ['required', 'numeric', 'min:0'],
            'records.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Exam|null $exam */
            $exam = $this->route('exam');
            if (! $exam) {
                return;
            }

            $maxMarks = (float) $exam->max_marks;
            $classId = (int) $exam->school_class_id;
            $studentIds = collect($this->input('records', []))->pluck('student_id')->unique();

            $validCount = Student::query()
                ->where('school_class_id', $classId)
                ->whereIn('id', $studentIds)
                ->count();

            if ($validCount !== $studentIds->count()) {
                $validator->errors()->add('records', 'All students must belong to the exam class.');
            }

            foreach ($this->input('records', []) as $index => $record) {
                if ((float) $record['marks_obtained'] > $maxMarks) {
                    $validator->errors()->add(
                        "records.{$index}.marks_obtained",
                        "Marks cannot exceed exam max marks ({$maxMarks})."
                    );
                }
            }
        });
    }
}
