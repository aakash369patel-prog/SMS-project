<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonPlan extends Model
{
    protected $fillable = [
        'syllabus_id',
        'school_class_id',
        'subject_id',
        'teacher_id',
        'title',
        'planned_date',
        'objectives',
        'content',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'planned_date' => 'date',
        ];
    }

    public function syllabus(): BelongsTo
    {
        return $this->belongsTo(Syllabus::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
