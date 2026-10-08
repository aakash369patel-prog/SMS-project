<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlinePaymentOrder extends Model
{
    protected $fillable = [
        'student_fee_id',
        'user_id',
        'provider',
        'amount',
        'currency',
        'status',
        'external_order_id',
        'external_payment_id',
        'meta',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'meta' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function studentFee(): BelongsTo
    {
        return $this->belongsTo(StudentFee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
