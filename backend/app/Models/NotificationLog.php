<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'channel',
        'notification_type',
        'recipient_email',
        'recipient_phone',
        'subject',
        'body',
        'status',
        'meta',
        'sent_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
