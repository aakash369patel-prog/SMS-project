<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentPromotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function history(Request $request)
    {
        $query = StudentPromotion::query()
            ->with(['student', 'fromClass', 'toClass'])
            ->latest('promoted_at');

        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->string('academic_year'));
        }

        return response()->json($query->paginate(30));
    }

    public function bulkPromote(Request $request)
    {
        $data = $request->validate([
            'from_class_id' => ['required', 'exists:school_classes,id'],
            'to_class_id' => ['required', 'exists:school_classes,id', 'different:from_class_id'],
            'academic_year' => ['required', 'string', 'max:20'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $fromClass = SchoolClass::query()->findOrFail($data['from_class_id']);
        $toClass = SchoolClass::query()->findOrFail($data['to_class_id']);

        $studentsQuery = Student::query()->where('school_class_id', $fromClass->id);
        if (! empty($data['student_ids'])) {
            $studentsQuery->whereIn('id', $data['student_ids']);
        }
        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            return response()->json(['message' => 'No students to promote.'], 422);
        }

        $userId = $request->user()?->id;
        $promoted = [];

        DB::transaction(function () use ($students, $fromClass, $toClass, $data, $userId, &$promoted) {
            foreach ($students as $student) {
                StudentPromotion::query()->create([
                    'student_id' => $student->id,
                    'from_class_id' => $fromClass->id,
                    'to_class_id' => $toClass->id,
                    'academic_year' => $data['academic_year'],
                    'promoted_at' => now(),
                    'promoted_by' => $userId,
                ]);
                $student->update(['school_class_id' => $toClass->id]);
                $promoted[] = $student->fresh(['schoolClass']);
            }
        });

        return response()->json([
            'message' => count($promoted).' student(s) promoted to '.$toClass->name.' '.$toClass->section.'.',
            'data' => $promoted,
        ]);
    }
}
