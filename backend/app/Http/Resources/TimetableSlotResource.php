<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimetableSlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_class_id' => $this->school_class_id,
            'day_of_week' => $this->day_of_week,
            'period_number' => $this->period_number,
            'start_time' => $this->start_time ? substr((string) $this->start_time, 0, 5) : null,
            'end_time' => $this->end_time ? substr((string) $this->end_time, 0, 5) : null,
            'subject_id' => $this->subject_id,
            'teacher_id' => $this->teacher_id,
            'room' => $this->room,
            'subject' => SubjectResource::make($this->whenLoaded('subject')),
            'teacher' => TeacherResource::make($this->whenLoaded('teacher')),
            'school_class' => SchoolClassResource::make($this->whenLoaded('schoolClass')),
            'label' => $this->when(
                $this->relationLoaded('subject'),
                fn () => trim(collect([
                    $this->subject?->name,
                    $this->teacher?->full_name ?? null,
                    $this->room,
                ])->filter()->implode(' · '))
            ),
        ];
    }
}
