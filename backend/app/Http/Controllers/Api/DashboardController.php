<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamGrade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableSlot;
use App\Models\ParentGuardian;
use App\Models\FeeType;
use App\Models\StudentFee;
use App\Models\Announcement;
use App\Models\FeePayment;

class DashboardController extends Controller
{
    public function __invoke(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'stats' => [
                'students' => Student::query()->count(),
                'teachers' => Teacher::query()->count(),
                'classes' => SchoolClass::query()->count(),
                'subjects' => Subject::query()->count(),
                'attendance_marked_today' => Attendance::query()
                    ->whereDate('attendance_date', now()->toDateString())
                    ->count(),
                'exams' => Exam::query()->count(),
                'grades_recorded' => ExamGrade::query()->count(),
                'timetable_slots' => TimetableSlot::query()->count(),
                'parents' => ParentGuardian::query()->count(),
                'fee_types' => FeeType::query()->count(),
                'open_fees' => StudentFee::query()->whereIn('status', ['pending', 'partial'])->count(),
                'announcements' => Announcement::query()->where('is_published', true)->count(),
            ],
            'charts' => [
                'attendance_trends' => $this->attendanceTrends(),
                'fee_collection' => $this->feeCollection(),
                'pass_rates' => $this->passRates(),
            ],
        ]);
    }

    /** @return list<array{label: string, present: int, absent: int}> */
    private function attendanceTrends(): array
    {
        $rows = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $rows[] = [
                'label' => now()->subDays($i)->format('D'),
                'present' => Attendance::query()->whereDate('attendance_date', $date)->where('status', 'present')->count(),
                'absent' => Attendance::query()->whereDate('attendance_date', $date)->where('status', 'absent')->count(),
            ];
        }

        return $rows;
    }

    /** @return list<array{label: string, value: float}> */
    private function feeCollection(): array
    {
        $rows = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $total = (float) FeePayment::query()
                ->whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->sum('amount');
            $rows[] = [
                'label' => $month->format('M'),
                'value' => round($total, 2),
            ];
        }

        return $rows;
    }

    /** @return list<array{label: string, value: float}> */
    private function passRates(): array
    {
        return Exam::query()
            ->withCount('grades')
            ->latest('exam_date')
            ->limit(5)
            ->get()
            ->map(function (Exam $exam) {
                $grades = ExamGrade::query()->where('exam_id', $exam->id)->get();
                if ($grades->isEmpty()) {
                    return ['label' => $exam->code, 'value' => 0];
                }
                $max = (float) $exam->max_marks ?: 100;
                $passed = $grades->filter(fn ($g) => ((float) $g->marks_obtained / $max) * 100 >= 50)->count();

                return [
                    'label' => $exam->code,
                    'value' => round(($passed / $grades->count()) * 100, 1),
                ];
            })
            ->values()
            ->all();
    }
}
