<?php

namespace App\Http\Requests;

use App\Models\TimetableSlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BulkTimetableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'slots' => ['required', 'array'],
            'slots.*.day_of_week' => ['required', Rule::in(TimetableSlot::DAYS)],
            'slots.*.period_number' => ['required', 'integer', 'min:1', 'max:12'],
            'slots.*.start_time' => ['nullable', 'date_format:H:i'],
            'slots.*.end_time' => ['nullable', 'date_format:H:i'],
            'slots.*.subject_id' => ['nullable', 'exists:subjects,id'],
            'slots.*.teacher_id' => ['nullable', 'exists:teachers,id'],
            'slots.*.room' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $classId = (int) $this->input('school_class_id');

            foreach ($this->input('slots', []) as $index => $slot) {
                $hasAssignment = ! empty($slot['subject_id']) || ! empty($slot['teacher_id']) || ! empty($slot['room']);
                if (! $hasAssignment) {
                    continue;
                }

                if (empty($slot['subject_id'])) {
                    $validator->errors()->add("slots.{$index}.subject_id", 'Subject is required when scheduling a period.');
                }
            }

            $subjectIds = collect($this->input('slots', []))
                ->pluck('subject_id')
                ->filter()
                ->unique();

            if ($subjectIds->isNotEmpty()) {
                $validSubjects = \App\Models\Subject::query()
                    ->where('school_class_id', $classId)
                    ->whereIn('id', $subjectIds)
                    ->count();

                if ($validSubjects !== $subjectIds->count()) {
                    $validator->errors()->add('slots', 'Subjects must belong to the selected class.');
                }
            }
        });
    }
}
