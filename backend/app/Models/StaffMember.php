<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffMember extends Model
{
    protected $fillable = [
        'user_id', 'employee_code', 'first_name', 'last_name', 'email', 'phone',
        'department', 'job_title', 'hire_date', 'basic_salary', 'status',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'basic_salary' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payrollEntries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
