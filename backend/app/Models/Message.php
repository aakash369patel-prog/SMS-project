<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'message_thread_id',
        'sender_user_id',
        'body',
        'moderation_status',
        'moderated_by',
        'moderated_at',
        'visible_to_parent',
    ];

    protected function casts(): array
    {
        return [
            'moderated_at' => 'datetime',
            'visible_to_parent' => 'boolean',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(MessageThread::class, 'message_thread_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }
}
