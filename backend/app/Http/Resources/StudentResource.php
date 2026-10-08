<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'admission_number' => $this->admission_number,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => trim("{$this->first_name} {$this->last_name}"),
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'gender' => $this->gender,
            'school_class_id' => $this->school_class_id,
            'photo_url' => $this->photo_url,
            'school_class' => SchoolClassResource::make($this->whenLoaded('schoolClass')),
            'documents' => $this->whenLoaded('documents'),
            'pivot' => $this->when($this->pivot, fn () => [
                'relation' => $this->pivot->relation ?? null,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
