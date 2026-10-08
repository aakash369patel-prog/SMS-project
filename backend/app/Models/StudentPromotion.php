<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPromotion extends Model
{
    protected $fillable = [
        'student_id',
        'from_class_id',
        'to_class_id',
        'academic_year',
        'promoted_at',
        'promoted_by',
    ];

    protected function casts(): array
    {
        return [
            'promoted_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'from_class_id');
    }

    public function toClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'to_class_id');
    }

    public function promotedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }
}
