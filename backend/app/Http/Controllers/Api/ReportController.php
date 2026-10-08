<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamGrade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function reportCardPdf(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ]);

        $student = Student::query()->with('schoolClass')->findOrFail($data['student_id']);
        $grades = ExamGrade::query()
            ->with(['exam.subject'])
            ->where('student_id', $student->id)
            ->get();

        $rows = $grades->map(function (ExamGrade $grade) {
            $exam = $grade->exam;
            $max = (float) ($exam?->max_marks ?? 100);
            $marks = (float) $grade->marks_obtained;
            $pct = $max > 0 ? round(($marks / $max) * 100, 2) : 0;

            return [
                'exam' => $exam?->name ?? '—',
                'subject' => $exam?->subject?->name ?? '—',
                'marks' => $marks,
                'max' => $max,
                'percentage' => $pct,
                'letter' => Exam::letterGrade($pct),
            ];
        })->all();

        $pdf = Pdf::loadView('reports.report_card', compact('student', 'rows'));

        return $pdf->download("report-card-{$student->admission_number}.pdf");
    }

    public function attendanceSheetPdf(Request $request)
    {
        $data = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $schoolClass = SchoolClass::query()->findOrFail($data['school_class_id']);
        $classLabel = trim("{$schoolClass->name} {$schoolClass->section}");
        $from = $data['from'];
        $to = $data['to'];

        $students = $schoolClass->students()->orderBy('first_name')->get();
        $rows = $students->map(function (Student $student) use ($data) {
            $records = Attendance::query()
                ->where('student_id', $student->id)
                ->where('school_class_id', $data['school_class_id'])
                ->whereDate('attendance_date', '>=', $data['from'])
                ->whereDate('attendance_date', '<=', $data['to'])
                ->get();

            return [
                'name' => trim("{$student->first_name} {$student->last_name}"),
                'present' => $records->where('status', 'present')->count(),
                'absent' => $records->where('status', 'absent')->count(),
                'late' => $records->where('status', 'late')->count(),
                'excused' => $records->where('status', 'excused')->count(),
            ];
        })->all();

        $pdf = Pdf::loadView('reports.attendance_sheet', compact('classLabel', 'from', 'to', 'rows'));

        return $pdf->download("attendance-{$schoolClass->id}.pdf");
    }

    public function feeReceiptPdf(Request $request)
    {
        $data = $request->validate([
            'student_fee_id' => ['required', 'exists:student_fees,id'],
        ]);

        $fee = StudentFee::query()->with(['student', 'payments' => fn ($q) => $q->latest()->limit(1)])->findOrFail($data['student_fee_id']);
        $payment = $fee->payments->first();

        $pdf = Pdf::loadView('reports.fee_receipt', compact('fee', 'payment'));

        return $pdf->download("fee-receipt-{$fee->id}.pdf");
    }
}
