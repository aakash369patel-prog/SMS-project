<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Syllabus extends Model
{
    protected $table = 'syllabi';

    protected $fillable = [
        'school_class_id',
        'subject_id',
        'academic_year',
        'title',
        'overview',
        'units',
    ];

    protected function casts(): array
    {
        return [
            'units' => 'array',
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

    public function lessonPlans(): HasMany
    {
        return $this->hasMany(LessonPlan::class);
    }
}
