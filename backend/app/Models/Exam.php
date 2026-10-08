<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    public const TYPES = ['quiz', 'unit_test', 'midterm', 'final', 'practical'];

    protected $fillable = [
        'name',
        'code',
        'school_class_id',
        'subject_id',
        'exam_type',
        'exam_date',
        'max_marks',
        'academic_year',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'max_marks' => 'decimal:2',
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

    public function grades(): HasMany
    {
        return $this->hasMany(ExamGrade::class);
    }

    public static function letterGrade(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B',
            $percentage >= 60 => 'C',
            $percentage >= 50 => 'D',
            default => 'F',
        };
    }
}
