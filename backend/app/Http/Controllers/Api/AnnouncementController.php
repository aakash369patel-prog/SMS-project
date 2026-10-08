<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AnnouncementController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'audience' => ['nullable', 'string'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'published_only' => ['nullable', 'boolean'],
        ]);

        $query = Announcement::query()->with('schoolClass')->latest('published_at');

        if (! empty($filters['audience'])) {
            $query->where('audience', $filters['audience']);
        }

        if (! empty($filters['school_class_id'])) {
            $query->where(function ($q) use ($filters): void {
                $q->whereNull('school_class_id')
                    ->orWhere('school_class_id', $filters['school_class_id']);
            });
        }

        if (! empty($filters['published_only'])) {
            $query->where('is_published', true);
        }

        return AnnouncementResource::collection($query->paginate(15));
    }

    public function store(StoreAnnouncementRequest $request): AnnouncementResource
    {
        $data = $request->validated();
        $announcement = Announcement::query()->create([
            ...$data,
            'published_at' => $data['published_at'] ?? now(),
            'created_by' => $request->user()->id,
        ]);

        $announcement->load('schoolClass');

        return new AnnouncementResource($announcement);
    }

    public function show(Announcement $announcement): AnnouncementResource
    {
        $announcement->load('schoolClass');

        return new AnnouncementResource($announcement);
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): AnnouncementResource
    {
        $announcement->update($request->validated());
        $announcement->load('schoolClass');

        return new AnnouncementResource($announcement);
    }

    public function destroy(Announcement $announcement): \Illuminate\Http\JsonResponse
    {
        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully.']);
    }

    public function uploadAttachment(Request $request, Announcement $announcement): AnnouncementResource
    {
        $request->validate([
            'attachment' => ['required', 'file', 'max:5120'],
        ]);

        if ($announcement->attachment_path) {
            Storage::disk('public')->delete($announcement->attachment_path);
        }

        $file = $request->file('attachment');
        $path = $file->store('announcements/attachments', 'public');
        $announcement->update([
            'attachment_path' => $path,
            'attachment_name' => $file->getClientOriginalName(),
        ]);

        return new AnnouncementResource($announcement->load('schoolClass'));
    }

    public function feed(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $classIds = [];

        if ($user->isParent() && $user->parentProfile) {
            $classIds = $user->parentProfile->students()->pluck('school_class_id')->filter()->unique()->values()->all();
        }

        $query = Announcement::query()
            ->with('schoolClass')
            ->where('is_published', true)
            ->latest('published_at');

        if ($user->isParent()) {
            $query->whereIn('audience', ['all', 'parents']);
        } elseif ($user->isAdmin()) {
            // Admin feed shows all published
        }

        if ($classIds !== []) {
            $query->where(function ($q) use ($classIds): void {
                $q->whereNull('school_class_id')->orWhereIn('school_class_id', $classIds);
            });
        }

        return AnnouncementResource::collection($query->paginate(20));
    }
}
