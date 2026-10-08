<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\Teacher;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeacherController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $teachers = Teacher::query()->latest()->paginate(15);

        return TeacherResource::collection($teachers);
    }

    public function store(StoreTeacherRequest $request): TeacherResource
    {
        $teacher = Teacher::query()->create($request->validated());

        return new TeacherResource($teacher);
    }

    public function show(Teacher $teacher): TeacherResource
    {
        return new TeacherResource($teacher);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): TeacherResource
    {
        $teacher->update($request->validated());

        return new TeacherResource($teacher);
    }

    public function destroy(Teacher $teacher): \Illuminate\Http\JsonResponse
    {
        $teacher->delete();

        return response()->json(['message' => 'Teacher deleted successfully.']);
    }
}
