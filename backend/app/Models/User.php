<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function parentProfile(): HasOne
    {
        return $this->hasOne(ParentGuardian::class, 'user_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isStaffMember(): bool
    {
        return in_array($this->role, ['admin', 'teacher', 'staff'], true);
    }

    public function teacherProfile(): HasOne
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    public function canAbility(string $ability): bool
    {
        return \App\Support\Permission::allows($this, $ability);
    }
}
