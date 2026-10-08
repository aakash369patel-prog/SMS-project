<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'exam_type' => $this->exam_type,
            'exam_date' => $this->exam_date?->toDateString(),
            'max_marks' => (float) $this->max_marks,
            'academic_year' => $this->academic_year,
            'description' => $this->description,
            'school_class_id' => $this->school_class_id,
            'subject_id' => $this->subject_id,
            'school_class' => SchoolClassResource::make($this->whenLoaded('schoolClass')),
            'subject' => SubjectResource::make($this->whenLoaded('subject')),
            'grades_count' => $this->whenCounted('grades'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
