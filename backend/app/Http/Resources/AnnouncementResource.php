<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'attachment_name' => $this->attachment_name,
            'attachment_url' => $this->attachment_url,
            'audience' => $this->audience,
            'school_class_id' => $this->school_class_id,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->toISOString(),
            'school_class' => SchoolClassResource::make($this->whenLoaded('schoolClass')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
