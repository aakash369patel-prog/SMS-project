<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkExamGradeRequest;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Http\Resources\ExamGradeResource;
use App\Http\Resources\ExamResource;
use App\Http\Resources\StudentResource;
use App\Models\Exam;
use App\Models\ExamGrade;
use App\Support\AuditLogger;
use App\Support\TeacherAccess;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'exam_type' => ['nullable', 'string'],
        ]);

        $query = Exam::query()
            ->with(['schoolClass', 'subject'])
            ->withCount('grades')
            ->latest('exam_date');

        if (! empty($filters['school_class_id'])) {
            $query->where('school_class_id', $filters['school_class_id']);
        }

        if (! empty($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (! empty($filters['exam_type'])) {
            $query->where('exam_type', $filters['exam_type']);
        }

        $classIds = TeacherAccess::examQueryClassIds($request->user());
        if ($classIds !== null) {
            $query->whereIn('school_class_id', $classIds ?: [-1]);
        }

        return ExamResource::collection($query->paginate(15));
    }

    public function store(StoreExamRequest $request): ExamResource
    {
        TeacherAccess::assertCanAccessClass($request->user(), (int) $request->input('school_class_id'));

        $exam = Exam::query()->create($request->validated());
        $exam->load(['schoolClass', 'subject']);

        return new ExamResource($exam);
    }

    public function show(Exam $exam): ExamResource
    {
        TeacherAccess::assertCanManageExam(request()->user(), $exam);

        $exam->load(['schoolClass', 'subject']);
        $exam->loadCount('grades');

        return new ExamResource($exam);
    }

    public function update(UpdateExamRequest $request, Exam $exam): ExamResource
    {
        TeacherAccess::assertCanManageExam($request->user(), $exam);

        if ($request->filled('school_class_id')) {
            TeacherAccess::assertCanAccessClass($request->user(), (int) $request->input('school_class_id'));
        }

        $exam->update($request->validated());
        $exam->load(['schoolClass', 'subject']);
        $exam->loadCount('grades');

        return new ExamResource($exam);
    }

    public function destroy(Exam $exam): \Illuminate\Http\JsonResponse
    {
        TeacherAccess::assertCanManageExam(request()->user(), $exam);

        $exam->delete();

        return response()->json(['message' => 'Exam deleted successfully.']);
    }

    public function sheet(Exam $exam): \Illuminate\Http\JsonResponse
    {
        TeacherAccess::assertCanManageExam(request()->user(), $exam);

        $exam->load(['schoolClass', 'subject']);

        $students = $exam->schoolClass
            ->students()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $grades = ExamGrade::query()
            ->where('exam_id', $exam->id)
            ->get()
            ->keyBy('student_id');

        return response()->json([
            'exam' => new ExamResource($exam),
            'rows' => $students->map(fn ($student) => [
                'student' => new StudentResource($student),
                'grade' => isset($grades[$student->id])
                    ? (new ExamGradeResource($grades[$student->id]))->additional(['max_marks' => (float) $exam->max_marks])
                    : null,
            ])->values(),
        ]);
    }

    public function bulkGrades(BulkExamGradeRequest $request, Exam $exam): \Illuminate\Http\JsonResponse
    {
        TeacherAccess::assertCanManageExam($request->user(), $exam);

        $data = $request->validated();
        $userId = $request->user()->id;

        DB::transaction(function () use ($data, $exam, $userId, $request): void {
            foreach ($data['records'] as $record) {
                $existing = ExamGrade::query()->where('exam_id', $exam->id)->where('student_id', $record['student_id'])->first();
                $old = $existing ? ['marks_obtained' => $existing->marks_obtained] : null;

                $grade = ExamGrade::query()->updateOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'student_id' => $record['student_id'],
                    ],
                    [
                        'marks_obtained' => $record['marks_obtained'],
                        'remarks' => $record['remarks'] ?? null,
                        'recorded_by' => $userId,
                    ]
                );

                AuditLogger::log(
                    $request->user(),
                    'grade.updated',
                    $grade,
                    $old,
                    ['marks_obtained' => $grade->marks_obtained, 'exam_id' => $exam->id],
                    $request
                );
            }
        });

        return response()->json(['message' => 'Grades saved successfully.']);
    }

    public function summary(Exam $exam): \Illuminate\Http\JsonResponse
    {
        TeacherAccess::assertCanManageExam(request()->user(), $exam);

        $exam->loadCount('grades');
        $maxMarks = (float) $exam->max_marks;

        $grades = ExamGrade::query()->where('exam_id', $exam->id)->get();
        $studentTotal = $exam->schoolClass()->withCount('students')->first()?->students_count ?? 0;

        $percentages = $grades->map(fn (ExamGrade $g) => $maxMarks > 0 ? ((float) $g->marks_obtained / $maxMarks) * 100 : 0);

        $passed = $percentages->filter(fn ($p) => $p >= 50)->count();
        $average = $grades->isEmpty() ? 0 : round($percentages->avg(), 2);
        $highest = $grades->isEmpty() ? 0 : round($percentages->max(), 2);
        $lowest = $grades->isEmpty() ? 0 : round($percentages->min(), 2);

        return response()->json([
            'exam_id' => $exam->id,
            'max_marks' => $maxMarks,
            'students_in_class' => $studentTotal,
            'graded' => $grades->count(),
            'passed' => $passed,
            'failed' => max(0, $grades->count() - $passed),
            'average_percentage' => $average,
            'highest_percentage' => $highest,
            'lowest_percentage' => $lowest,
        ]);
    }
}
