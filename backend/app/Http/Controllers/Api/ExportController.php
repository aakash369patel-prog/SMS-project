<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\StudentFee;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function students(): StreamedResponse
    {
        $filename = 'students-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Admission', 'First Name', 'Last Name', 'Email', 'Class ID', 'Gender']);
            Student::query()->with('schoolClass')->orderBy('id')->chunk(200, function ($chunk) use ($out): void {
                foreach ($chunk as $student) {
                    fputcsv($out, [
                        $student->admission_number,
                        $student->first_name,
                        $student->last_name,
                        $student->email,
                        $student->schoolClass?->label ?? $student->school_class_id,
                        $student->gender,
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function attendance(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $filename = 'attendance-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($filters): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Class ID', 'Student ID', 'Admission', 'Status', 'Remark']);
            $query = Attendance::query()->with('student')->orderBy('attendance_date');
            if (! empty($filters['school_class_id'])) {
                $query->where('school_class_id', $filters['school_class_id']);
            }
            if (! empty($filters['from'])) {
                $query->whereDate('attendance_date', '>=', $filters['from']);
            }
            if (! empty($filters['to'])) {
                $query->whereDate('attendance_date', '<=', $filters['to']);
            }
            $query->chunk(200, function ($chunk) use ($out): void {
                foreach ($chunk as $row) {
                    fputcsv($out, [
                        $row->attendance_date?->format('Y-m-d'),
                        $row->school_class_id,
                        $row->student_id,
                        $row->student?->admission_number,
                        $row->status,
                        $row->remark,
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function fees(): StreamedResponse
    {
        $filename = 'student-fees-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Student ID', 'Title', 'Amount', 'Paid', 'Balance', 'Status', 'Due Date']);
            StudentFee::query()->orderBy('id')->chunk(200, function ($chunk) use ($out): void {
                foreach ($chunk as $fee) {
                    fputcsv($out, [
                        $fee->student_id,
                        $fee->title,
                        $fee->amount,
                        $fee->amount_paid,
                        max(0, (float) $fee->amount - (float) $fee->amount_paid),
                        $fee->status,
                        $fee->due_date?->format('Y-m-d'),
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
