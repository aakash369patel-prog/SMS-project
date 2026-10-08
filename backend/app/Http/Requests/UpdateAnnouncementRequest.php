<?php

namespace App\Http\Requests;

use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:200'],
            'body' => ['sometimes', 'string'],
            'audience' => ['sometimes', Rule::in(Announcement::AUDIENCES)],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
