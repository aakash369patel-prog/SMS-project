<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableSlot extends Model
{
    public const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

    public const PERIOD_COUNT = 6;

    /** @var array<int, array{start: string, end: string}> */
    public const DEFAULT_PERIOD_TIMES = [
        1 => ['start' => '08:00', 'end' => '08:45'],
        2 => ['start' => '08:50', 'end' => '09:35'],
        3 => ['start' => '09:50', 'end' => '10:35'],
        4 => ['start' => '10:40', 'end' => '11:25'],
        5 => ['start' => '11:30', 'end' => '12:15'],
        6 => ['start' => '13:00', 'end' => '13:45'],
    ];

    protected $fillable = [
        'school_class_id',
        'day_of_week',
        'period_number',
        'start_time',
        'end_time',
        'subject_id',
        'teacher_id',
        'room',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
