<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookLoan extends Model
{
    protected $fillable = [
        'book_id', 'student_id', 'issued_date', 'due_date', 'returned_date',
        'status', 'fine_amount', 'issued_by',
    ];

    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
            'due_date' => 'date',
            'returned_date' => 'date',
            'fine_amount' => 'decimal:2',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
