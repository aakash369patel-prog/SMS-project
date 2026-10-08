<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'school_class_id' => $this->school_class_id,
            'attendance_date' => $this->attendance_date?->toDateString(),
            'status' => $this->status,
            'remark' => $this->remark,
            'recorded_by' => $this->recorded_by,
            'student' => StudentResource::make($this->whenLoaded('student')),
            'school_class' => SchoolClassResource::make($this->whenLoaded('schoolClass')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
