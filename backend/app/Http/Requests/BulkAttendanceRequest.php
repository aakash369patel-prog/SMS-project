<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BulkAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'date' => ['required', 'date'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'records.*.status' => ['required', Rule::in(Attendance::STATUSES)],
            'records.*.remark' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $classId = (int) $this->input('school_class_id');
            $studentIds = collect($this->input('records', []))->pluck('student_id')->unique();

            $validCount = Student::query()
                ->where('school_class_id', $classId)
                ->whereIn('id', $studentIds)
                ->count();

            if ($validCount !== $studentIds->count()) {
                $validator->errors()->add('records', 'All students must belong to the selected class.');
            }
        });
    }
}
