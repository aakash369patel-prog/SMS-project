<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Announcement extends Model
{
    protected $appends = ['attachment_url'];
    public const AUDIENCES = ['all', 'parents', 'teachers', 'students', 'staff'];

    protected $fillable = [
        'title',
        'body',
        'attachment_path',
        'attachment_name',
        'audience',
        'school_class_id',
        'is_published',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        return Storage::disk('public')->url($this->attachment_path);
    }
}
