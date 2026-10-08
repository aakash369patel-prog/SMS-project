<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineQuizAnswer extends Model
{
    protected $fillable = [
        'online_quiz_attempt_id',
        'online_quiz_question_id',
        'answer',
        'is_correct',
        'marks_awarded',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'marks_awarded' => 'decimal:2',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(OnlineQuizAttempt::class, 'online_quiz_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(OnlineQuizQuestion::class, 'online_quiz_question_id');
    }
}
