<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineQuizQuestion extends Model
{
    protected $fillable = [
        'online_quiz_id',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'marks',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'marks' => 'decimal:2',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(OnlineQuiz::class, 'online_quiz_id');
    }
}
