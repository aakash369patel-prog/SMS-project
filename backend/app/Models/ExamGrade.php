<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamGrade extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'marks_obtained',
        'remarks',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'marks_obtained' => 'decimal:2',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
