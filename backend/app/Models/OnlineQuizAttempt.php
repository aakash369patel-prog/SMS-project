<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnlineQuizAttempt extends Model
{
    protected $fillable = [
        'online_quiz_id',
        'student_id',
        'started_at',
        'submitted_at',
        'score',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score' => 'decimal:2',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(OnlineQuiz::class, 'online_quiz_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(OnlineQuizAnswer::class);
    }
}
