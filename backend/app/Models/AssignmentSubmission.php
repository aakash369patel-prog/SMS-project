<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    public const STATUSES = ['pending', 'submitted', 'late', 'graded'];

    protected $fillable = [
        'assignment_id',
        'student_id',
        'status',
        'content',
        'submitted_at',
        'marks',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'marks' => 'decimal:2',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
