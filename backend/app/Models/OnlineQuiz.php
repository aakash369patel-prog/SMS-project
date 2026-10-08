<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnlineQuiz extends Model
{
    protected $fillable = [
        'title',
        'school_class_id',
        'subject_id',
        'duration_minutes',
        'starts_at',
        'ends_at',
        'is_published',
        'max_score',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_published' => 'boolean',
            'max_score' => 'decimal:2',
        ];
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(OnlineQuizQuestion::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(OnlineQuizAttempt::class);
    }
}
