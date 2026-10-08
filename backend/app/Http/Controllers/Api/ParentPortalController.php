<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\StudentFeeResource;
use App\Http\Resources\StudentResource;
use App\Models\Announcement;
use App\Models\CalendarEvent;
use App\Models\Exam;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\NotificationLog;
use App\Models\StudentFee;
use Illuminate\Http\Request;

class ParentPortalController extends Controller
{
    public function overview(Request $request): \Illuminate\Http\JsonResponse
    {
        $profile = $request->user()->parentProfile;
        if (! $profile) {
            return response()->json(['message' => 'Parent profile not found.'], 404);
        }

        $students = $profile->students()->with('schoolClass')->get();
        $studentIds = $students->pluck('id');

        $feeSummary = [
            'total_due' => (float) StudentFee::query()
                ->whereIn('student_id', $studentIds)
                ->whereIn('status', ['pending', 'partial'])
                ->selectRaw('SUM(amount - amount_paid) as due')
                ->value('due'),
            'open_invoices' => StudentFee::query()
                ->whereIn('student_id', $studentIds)
                ->whereIn('status', ['pending', 'partial'])
                ->count(),
        ];

        return response()->json([
            'parent' => [
                'id' => $profile->id,
                'full_name' => $profile->full_name,
                'email' => $profile->email,
            ],
            'children' => $students
                ->map(fn ($student) => (new StudentResource($student))->resolve(request()))
                ->values(),
            'fee_summary' => $feeSummary,
        ]);
    }

    public function fees(Request $request): \Illuminate\Http\JsonResponse
    {
        $profile = $request->user()->parentProfile;
        if (! $profile) {
            return response()->json(['message' => 'Parent profile not found.'], 404);
        }

        $studentIds = $profile->students()->pluck('students.id');
        $fees = StudentFee::query()
            ->with(['student', 'feeType', 'payments'])
            ->whereIn('student_id', $studentIds)
            ->latest()
            ->paginate(20);

        return StudentFeeResource::collection($fees)->response();
    }

    public function announcements(Request $request): \Illuminate\Http\JsonResponse
    {
        $profile = $request->user()->parentProfile;
        if (! $profile) {
            return response()->json(['message' => 'Parent profile not found.'], 404);
        }

        $classIds = $profile->students()->pluck('school_class_id')->filter()->unique();

        $query = Announcement::query()
            ->with('schoolClass')
            ->where('is_published', true)
            ->whereIn('audience', ['all', 'parents'])
            ->latest('published_at');

        if ($classIds->isNotEmpty()) {
            $query->where(function ($q) use ($classIds): void {
                $q->whereNull('school_class_id')->orWhereIn('school_class_id', $classIds);
            });
        }

        return AnnouncementResource::collection($query->paginate(20))->response();
    }

    public function messages(Request $request): \Illuminate\Http\JsonResponse
    {
        $profile = $this->parentProfileOrFail($request);

        $threads = MessageThread::query()
            ->where('parent_id', $profile->id)
            ->with(['messages' => fn ($q) => $q->where('visible_to_parent', true)->latest()->limit(1)])
            ->latest()
            ->get();

        return response()->json(['data' => $threads]);
    }

    public function messageThread(Request $request, MessageThread $messageThread): \Illuminate\Http\JsonResponse
    {
        $profile = $this->parentProfileOrFail($request);
        if ($messageThread->parent_id !== $profile->id) {
            abort(403);
        }

        $messages = $messageThread->messages()
            ->with('sender')
            ->where('visible_to_parent', true)
            ->oldest()
            ->get();

        return response()->json([
            'data' => [
                'thread' => $messageThread->load('parentGuardian'),
                'messages' => $messages,
            ],
        ]);
    }

    public function replyMessage(Request $request, MessageThread $messageThread): \Illuminate\Http\JsonResponse
    {
        $profile = $this->parentProfileOrFail($request);
        if ($messageThread->parent_id !== $profile->id) {
            abort(403);
        }

        $data = $request->validate(['body' => ['required', 'string']]);
        $user = $request->user();

        $message = Message::query()->create([
            'message_thread_id' => $messageThread->id,
            'sender_user_id' => $user->id,
            'body' => $data['body'],
            'moderation_status' => 'approved',
            'visible_to_parent' => true,
            'moderated_at' => now(),
            'moderated_by' => $user->id,
        ]);

        return response()->json(['data' => $message->load('sender')], 201);
    }

    public function calendar(Request $request): \Illuminate\Http\JsonResponse
    {
        $profile = $this->parentProfileOrFail($request);
        $classIds = $profile->students()->pluck('school_class_id')->filter()->unique();

        $from = $request->date('from', now()->startOfMonth());
        $to = $request->date('to', now()->endOfMonth());

        $events = CalendarEvent::query()
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('starts_at', [$from, $to])
                    ->orWhereBetween('ends_at', [$from, $to]);
            })
            ->where(function ($q) use ($classIds) {
                $q->whereNull('school_class_id');
                if ($classIds->isNotEmpty()) {
                    $q->orWhereIn('school_class_id', $classIds);
                }
            })
            ->orderBy('starts_at')
            ->get();

        $exams = Exam::query()
            ->whereBetween('exam_date', [$from->toDateString(), $to->toDateString()])
            ->when($classIds->isNotEmpty(), fn ($q) => $q->whereIn('school_class_id', $classIds))
            ->get()
            ->map(fn (Exam $exam) => [
                'id' => 'exam-'.$exam->id,
                'title' => $exam->name,
                'event_type' => 'exam',
                'starts_at' => $exam->exam_date->startOfDay()->toIso8601String(),
                'all_day' => true,
                'source' => 'exam',
            ]);

        return response()->json([
            'data' => $events->map(fn ($e) => [...$e->toArray(), 'source' => 'calendar'])
                ->concat($exams)
                ->sortBy('starts_at')
                ->values(),
        ]);
    }

    public function notificationHistory(Request $request): \Illuminate\Http\JsonResponse
    {
        $profile = $this->parentProfileOrFail($request);
        $email = $profile->email ?: $request->user()->email;
        $phone = $profile->phone;

        $query = NotificationLog::query()->latest();
        $query->where(function ($q) use ($email, $phone) {
            if ($email) {
                $q->orWhere('recipient_email', $email);
            }
            if ($phone) {
                $q->orWhere('recipient_phone', $phone);
            }
        });

        return response()->json($query->paginate(20));
    }

    private function parentProfileOrFail(Request $request): \App\Models\ParentGuardian
    {
        $profile = $request->user()->parentProfile;
        if (! $profile) {
            abort(404, 'Parent profile not found.');
        }

        return $profile;
    }
}
