<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageThread extends Model
{
    protected $fillable = [
        'parent_id',
        'subject',
        'created_by',
    ];

    public function parentGuardian(): BelongsTo
    {
        return $this->belongsTo(ParentGuardian::class, 'parent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }
}
