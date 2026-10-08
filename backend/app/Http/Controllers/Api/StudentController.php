<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
        ]);

        $students = Student::query()
            ->with('schoolClass')
            ->when($filters['q'] ?? null, function ($query, string $term): void {
                $like = '%'.$term.'%';
                $query->where(function ($q) use ($like): void {
                    $q->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('admission_number', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->when($filters['school_class_id'] ?? null, fn ($q, $classId) => $q->where('school_class_id', $classId))
            ->latest()
            ->paginate(15);

        return StudentResource::collection($students);
    }

    public function store(StoreStudentRequest $request): StudentResource
    {
        $student = Student::query()->create($request->validated());
        $student->load('schoolClass');

        return new StudentResource($student);
    }

    public function show(Student $student): StudentResource
    {
        $student->load('schoolClass');

        return new StudentResource($student);
    }

    public function update(UpdateStudentRequest $request, Student $student): StudentResource
    {
        $student->update($request->validated());
        $student->load('schoolClass');

        return new StudentResource($student);
    }

    public function destroy(Student $student): \Illuminate\Http\JsonResponse
    {
        $student->delete();

        return response()->json(['message' => 'Student deleted successfully.']);
    }
}
