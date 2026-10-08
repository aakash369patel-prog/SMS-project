<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolClassRequest;
use App\Http\Requests\UpdateSchoolClassRequest;
use App\Http\Resources\SchoolClassResource;
use App\Models\SchoolClass;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SchoolClassController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $classes = SchoolClass::query()
            ->with('classTeacher')
            ->withCount('students')
            ->latest()
            ->paginate(15);

        return SchoolClassResource::collection($classes);
    }

    public function store(StoreSchoolClassRequest $request): SchoolClassResource
    {
        $schoolClass = SchoolClass::query()->create($request->validated());
        $schoolClass->load('classTeacher');

        return new SchoolClassResource($schoolClass);
    }

    public function show(SchoolClass $schoolClass): SchoolClassResource
    {
        $schoolClass->load(['classTeacher', 'students']);

        return new SchoolClassResource($schoolClass);
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass): SchoolClassResource
    {
        $schoolClass->update($request->validated());
        $schoolClass->load('classTeacher');

        return new SchoolClassResource($schoolClass);
    }

    public function destroy(SchoolClass $schoolClass): \Illuminate\Http\JsonResponse
    {
        $schoolClass->delete();

        return response()->json(['message' => 'Class deleted successfully.']);
    }
}
