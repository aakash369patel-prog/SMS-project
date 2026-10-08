<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentFee extends Model
{
    public const STATUSES = ['pending', 'partial', 'paid', 'cancelled'];

    protected $fillable = [
        'student_id',
        'fee_type_id',
        'title',
        'amount',
        'discount_amount',
        'discount_type',
        'discount_note',
        'amount_paid',
        'due_date',
        'status',
        'academic_year',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function onlinePaymentOrders(): HasMany
    {
        return $this->hasMany(OnlinePaymentOrder::class);
    }

    public function effectiveAmount(): float
    {
        return max(0, (float) $this->amount - (float) ($this->discount_amount ?? 0));
    }

    public function balanceDue(): float
    {
        return max(0, round($this->effectiveAmount() - (float) $this->amount_paid, 2));
    }

    public function refreshPaymentTotals(): void
    {
        $paid = (float) $this->payments()->sum('amount');
        $amount = $this->effectiveAmount();

        $status = 'pending';
        if ($paid <= 0) {
            $status = 'pending';
        } elseif ($paid >= $amount) {
            $status = 'paid';
            $paid = $amount;
        } else {
            $status = 'partial';
        }

        if ($this->status === 'cancelled') {
            return;
        }

        $this->update([
            'amount_paid' => $paid,
            'status' => $status,
        ]);
    }
}
