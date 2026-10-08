<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Assignment::query()
            ->with(['schoolClass', 'subject', 'submissions'])
            ->latest();

        if ($request->filled('school_class_id')) {
            $query->where('school_class_id', $request->integer('school_class_id'));
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'due_date' => ['required', 'date'],
            'publish' => ['sometimes', 'boolean'],
        ]);

        $data['published_at'] = ! empty($data['publish']) ? now() : null;
        unset($data['publish']);

        $assignment = Assignment::query()->create($data);
        $assignment->load(['schoolClass', 'subject']);

        $students = Student::query()->where('school_class_id', $assignment->school_class_id)->pluck('id');
        foreach ($students as $studentId) {
            AssignmentSubmission::query()->firstOrCreate(
                ['assignment_id' => $assignment->id, 'student_id' => $studentId],
                ['status' => 'pending']
            );
        }

        return response()->json(['data' => $assignment->load('submissions')], 201);
    }

    public function update(Request $request, Assignment $assignment)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'due_date' => ['sometimes', 'date'],
            'publish' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('publish', $data)) {
            $data['published_at'] = $data['publish'] ? ($assignment->published_at ?? now()) : null;
            unset($data['publish']);
        }

        $assignment->update($data);

        return response()->json(['data' => $assignment->fresh(['schoolClass', 'subject', 'submissions'])]);
    }

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();

        return response()->json(['message' => 'Assignment deleted.']);
    }

    public function submissions(Assignment $assignment)
    {
        return response()->json([
            'data' => $assignment->submissions()->with('student')->get(),
        ]);
    }

    public function updateSubmission(Request $request, Assignment $assignment, AssignmentSubmission $submission)
    {
        if ($submission->assignment_id !== $assignment->id) {
            abort(404);
        }

        $data = $request->validate([
            'status' => ['sometimes', 'in:pending,submitted,late,graded'],
            'content' => ['nullable', 'string'],
            'marks' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (isset($data['status']) && in_array($data['status'], ['submitted', 'late', 'graded'], true)) {
            $data['submitted_at'] = $submission->submitted_at ?? now();
            if ($data['status'] === 'submitted' && $assignment->due_date->isPast()) {
                $data['status'] = 'late';
            }
        }

        $submission->update($data);

        return response()->json(['data' => $submission->load('student')]);
    }
}
