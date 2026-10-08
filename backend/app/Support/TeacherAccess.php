<?php

namespace App\Support;

use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class TeacherAccess
{
    public static function teacherFor(User $user): ?Teacher
    {
        if ($user->role !== 'teacher') {
            return null;
        }

        return $user->relationLoaded('teacherProfile')
            ? $user->teacherProfile
            : $user->teacherProfile()->first();
    }

    public static function canAccessClass(User $user, int $schoolClassId): bool
    {
        if ($user->isAdmin() || $user->role === 'staff') {
            return true;
        }

        $teacher = self::teacherFor($user);
        if (! $teacher) {
            return false;
        }

        if (SchoolClass::query()->whereKey($schoolClassId)->where('class_teacher_id', $teacher->id)->exists()) {
            return true;
        }

        return Subject::query()
            ->where('school_class_id', $schoolClassId)
            ->where('teacher_id', $teacher->id)
            ->exists();
    }

    public static function canManageExam(User $user, Exam $exam): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->role !== 'teacher') {
            return false;
        }

        $teacher = self::teacherFor($user);
        if (! $teacher) {
            return false;
        }

        if ($exam->subject_id) {
            $subjectTeacherId = Subject::query()->whereKey($exam->subject_id)->value('teacher_id');
            if ((int) $subjectTeacherId === (int) $teacher->id) {
                return true;
            }
        }

        return SchoolClass::query()
            ->whereKey($exam->school_class_id)
            ->where('class_teacher_id', $teacher->id)
            ->exists();
    }

    public static function assertCanAccessClass(User $user, int $schoolClassId): void
    {
        if (! self::canAccessClass($user, $schoolClassId)) {
            throw new AuthorizationException('You are not allowed to access this class.');
        }
    }

    public static function assertCanManageExam(User $user, Exam $exam): void
    {
        if (! self::canManageExam($user, $exam)) {
            throw new AuthorizationException('You are not allowed to manage this exam.');
        }
    }

    /** @return list<int>|null null = no filter (admin/staff see all) */
    public static function examQueryClassIds(User $user): ?array
    {
        if ($user->isAdmin() || $user->role === 'staff') {
            return null;
        }

        $teacher = self::teacherFor($user);
        if (! $teacher) {
            return [];
        }

        $classTeacherIds = SchoolClass::query()
            ->where('class_teacher_id', $teacher->id)
            ->pluck('id');

        $subjectClassIds = Subject::query()
            ->where('teacher_id', $teacher->id)
            ->pluck('school_class_id');

        return $classTeacherIds->merge($subjectClassIds)->unique()->values()->all();
    }
}
