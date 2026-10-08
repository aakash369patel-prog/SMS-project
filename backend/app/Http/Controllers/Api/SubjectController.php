<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubjectController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $subjects = Subject::query()
            ->with(['schoolClass', 'teacher'])
            ->latest()
            ->paginate(15);

        return SubjectResource::collection($subjects);
    }

    public function store(StoreSubjectRequest $request): SubjectResource
    {
        $subject = Subject::query()->create($request->validated());
        $subject->load(['schoolClass', 'teacher']);

        return new SubjectResource($subject);
    }

    public function show(Subject $subject): SubjectResource
    {
        $subject->load(['schoolClass', 'teacher']);

        return new SubjectResource($subject);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): SubjectResource
    {
        $subject->update($request->validated());
        $subject->load(['schoolClass', 'teacher']);

        return new SubjectResource($subject);
    }

    public function destroy(Subject $subject): \Illuminate\Http\JsonResponse
    {
        $subject->delete();

        return response()->json(['message' => 'Subject deleted successfully.']);
    }
}
