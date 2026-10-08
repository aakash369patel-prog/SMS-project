<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    public const METHODS = ['cash', 'bank', 'online', 'cheque'];

    protected $fillable = [
        'student_fee_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function studentFee(): BelongsTo
    {
        return $this->belongsTo(StudentFee::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
