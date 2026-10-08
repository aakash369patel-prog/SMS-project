<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\SchoolClassResource;
use App\Http\Resources\StudentResource;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Support\AuditLogger;
use App\Support\TeacherAccess;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'student_id' => ['nullable', 'exists:students,id'],
            'date' => ['nullable', 'date'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'status' => ['nullable', 'in:present,absent,late,excused'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = Attendance::query()
            ->with(['student', 'schoolClass'])
            ->latest('attendance_date');

        if (! empty($filters['school_class_id'])) {
            $query->where('school_class_id', $filters['school_class_id']);
        }

        if (! empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (! empty($filters['date'])) {
            $query->whereDate('attendance_date', $filters['date']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('attendance_date', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('attendance_date', '<=', $filters['to']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['q'])) {
            $like = '%'.$filters['q'].'%';
            $query->whereHas('student', function ($q) use ($like): void {
                $q->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('admission_number', 'like', $like);
            });
        }

        if ($request->user()->isTeacher()) {
            $classIds = TeacherAccess::examQueryClassIds($request->user()) ?: [-1];
            $query->whereIn('school_class_id', $classIds);
        }

        return AttendanceResource::collection($query->paginate(20));
    }

    public function sheet(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'date' => ['required', 'date'],
        ]);

        TeacherAccess::assertCanAccessClass($request->user(), (int) $data['school_class_id']);

        $schoolClass = SchoolClass::query()
            ->with(['students' => fn ($q) => $q->orderBy('first_name')->orderBy('last_name')])
            ->findOrFail($data['school_class_id']);

        $attendances = Attendance::query()
            ->where('school_class_id', $data['school_class_id'])
            ->whereDate('attendance_date', $data['date'])
            ->get()
            ->keyBy('student_id');

        return response()->json([
            'date' => $data['date'],
            'school_class' => new SchoolClassResource($schoolClass),
            'rows' => $schoolClass->students->map(fn ($student) => [
                'student' => new StudentResource($student),
                'attendance' => isset($attendances[$student->id])
                    ? new AttendanceResource($attendances[$student->id])
                    : null,
            ])->values(),
        ]);
    }

    public function bulkStore(BulkAttendanceRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        TeacherAccess::assertCanAccessClass($request->user(), (int) $data['school_class_id']);

        $userId = $request->user()->id;

        DB::transaction(function () use ($data, $userId, $request): void {
            foreach ($data['records'] as $record) {
                $existing = Attendance::query()
                    ->where('student_id', $record['student_id'])
                    ->whereDate('attendance_date', $data['date'])
                    ->first();
                $old = $existing ? ['status' => $existing->status] : null;

                $attendance = Attendance::query()->updateOrCreate(
                    [
                        'student_id' => $record['student_id'],
                        'attendance_date' => $data['date'],
                    ],
                    [
                        'school_class_id' => $data['school_class_id'],
                        'status' => $record['status'],
                        'remark' => $record['remark'] ?? null,
                        'recorded_by' => $userId,
                    ]
                );

                AuditLogger::log(
                    $request->user(),
                    'attendance.updated',
                    $attendance,
                    $old,
                    ['status' => $attendance->status, 'date' => $data['date']],
                    $request
                );
            }
        });

        return response()->json(['message' => 'Attendance saved successfully.']);
    }

    public function summary(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'date' => ['required', 'date'],
        ]);

        TeacherAccess::assertCanAccessClass($request->user(), (int) $data['school_class_id']);

        $counts = Attendance::query()
            ->where('school_class_id', $data['school_class_id'])
            ->whereDate('attendance_date', $data['date'])
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $studentTotal = SchoolClass::query()
            ->withCount('students')
            ->findOrFail($data['school_class_id'])
            ->students_count;

        return response()->json([
            'date' => $data['date'],
            'school_class_id' => (int) $data['school_class_id'],
            'students_in_class' => $studentTotal,
            'marked' => $counts->sum(),
            'by_status' => [
                'present' => (int) ($counts['present'] ?? 0),
                'absent' => (int) ($counts['absent'] ?? 0),
                'late' => (int) ($counts['late'] ?? 0),
                'excused' => (int) ($counts['excused'] ?? 0),
            ],
        ]);
    }
}
