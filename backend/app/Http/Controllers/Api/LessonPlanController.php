<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LessonPlan;
use Illuminate\Http\Request;

class LessonPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = LessonPlan::query()->with(['schoolClass', 'subject', 'teacher', 'syllabus'])->latest();

        if ($request->filled('school_class_id')) {
            $query->where('school_class_id', $request->integer('school_class_id'));
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->integer('subject_id'));
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'syllabus_id' => ['nullable', 'exists:syllabi,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'title' => ['required', 'string', 'max:200'],
            'planned_date' => ['nullable', 'date'],
            'objectives' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,published,completed'],
        ]);

        $plan = LessonPlan::query()->create($data);

        return response()->json(['data' => $plan->load(['schoolClass', 'subject', 'teacher'])], 201);
    }

    public function update(Request $request, LessonPlan $lessonPlan)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:200'],
            'planned_date' => ['nullable', 'date'],
            'objectives' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,published,completed'],
        ]);

        $lessonPlan->update($data);

        return response()->json(['data' => $lessonPlan->fresh(['schoolClass', 'subject', 'teacher'])]);
    }

    public function destroy(LessonPlan $lessonPlan)
    {
        $lessonPlan->delete();

        return response()->json(['message' => 'Lesson plan deleted.']);
    }
}
