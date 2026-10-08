<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolClassResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'section' => $this->section,
            'label' => trim("{$this->name} {$this->section}"),
            'academic_year' => $this->academic_year,
            'capacity' => $this->capacity,
            'class_teacher_id' => $this->class_teacher_id,
            'class_teacher' => TeacherResource::make($this->whenLoaded('classTeacher')),
            'students_count' => $this->whenCounted('students'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
