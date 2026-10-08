<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'department',
        'hire_date',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'class_teacher_id');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }
}
