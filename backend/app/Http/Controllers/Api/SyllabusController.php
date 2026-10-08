<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Syllabus;
use Illuminate\Http\Request;

class SyllabusController extends Controller
{
    public function index(Request $request)
    {
        $query = Syllabus::query()->with(['schoolClass', 'subject'])->latest();

        if ($request->filled('school_class_id')) {
            $query->where('school_class_id', $request->integer('school_class_id'));
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'academic_year' => ['required', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:200'],
            'overview' => ['nullable', 'string'],
            'units' => ['nullable', 'array'],
        ]);

        $syllabus = Syllabus::query()->updateOrCreate(
            [
                'school_class_id' => $data['school_class_id'],
                'subject_id' => $data['subject_id'],
                'academic_year' => $data['academic_year'],
            ],
            $data
        );

        return response()->json(['data' => $syllabus->load(['schoolClass', 'subject'])], 201);
    }

    public function update(Request $request, Syllabus $syllabus)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:200'],
            'overview' => ['nullable', 'string'],
            'units' => ['nullable', 'array'],
        ]);

        $syllabus->update($data);

        return response()->json(['data' => $syllabus->fresh(['schoolClass', 'subject'])]);
    }

    public function destroy(Syllabus $syllabus)
    {
        $syllabus->delete();

        return response()->json(['message' => 'Syllabus deleted.']);
    }
}
