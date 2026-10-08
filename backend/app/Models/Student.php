<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    protected $appends = ['full_name', 'photo_url'];

    protected $fillable = [
        'admission_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'school_class_id',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function examGrades(): HasMany
    {
        return $this->hasMany(ExamGrade::class);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(ParentGuardian::class, 'parent_student', 'student_id', 'parent_id')
            ->withPivot('relation')
            ->withTimestamps();
    }

    public function fees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->photo_path);
    }
}
