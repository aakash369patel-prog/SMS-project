<?php

namespace App\Http\Resources;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamGradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $maxMarks = $this->relationLoaded('exam')
            ? (float) $this->exam->max_marks
            : (float) ($this->additional['max_marks'] ?? 100);

        $marks = (float) $this->marks_obtained;
        $percentage = $maxMarks > 0 ? round(($marks / $maxMarks) * 100, 2) : 0;

        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'student_id' => $this->student_id,
            'marks_obtained' => $marks,
            'max_marks' => $maxMarks,
            'percentage' => $percentage,
            'letter_grade' => Exam::letterGrade($percentage),
            'passed' => $percentage >= 50,
            'remarks' => $this->remarks,
            'student' => StudentResource::make($this->whenLoaded('student')),
            'exam' => ExamResource::make($this->whenLoaded('exam')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
