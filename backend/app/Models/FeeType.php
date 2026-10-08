<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'amount',
        'academic_year',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function studentFees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }
}
