<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'school_class_id' => $this->school_class_id,
            'teacher_id' => $this->teacher_id,
            'school_class' => SchoolClassResource::make($this->whenLoaded('schoolClass')),
            'teacher' => TeacherResource::make($this->whenLoaded('teacher')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
