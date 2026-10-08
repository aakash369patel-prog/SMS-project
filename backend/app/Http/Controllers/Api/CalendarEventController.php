<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\Exam;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->date('from', now()->startOfMonth());
        $to = $request->date('to', now()->endOfMonth());

        $events = CalendarEvent::query()
            ->with(['schoolClass', 'exam'])
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('starts_at', [$from, $to])
                    ->orWhereBetween('ends_at', [$from, $to]);
            })
            ->orderBy('starts_at')
            ->get();

        $exams = Exam::query()
            ->with(['schoolClass', 'subject'])
            ->whereBetween('exam_date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(fn (Exam $exam) => [
                'id' => 'exam-'.$exam->id,
                'title' => $exam->name,
                'event_type' => 'exam',
                'starts_at' => $exam->exam_date->startOfDay()->toIso8601String(),
                'ends_at' => $exam->exam_date->endOfDay()->toIso8601String(),
                'all_day' => true,
                'description' => $exam->description,
                'school_class_id' => $exam->school_class_id,
                'exam_id' => $exam->id,
                'source' => 'exam',
            ]);

        $mapped = $events->map(fn (CalendarEvent $e) => [
            ...$e->toArray(),
            'source' => 'calendar',
        ]);

        return response()->json([
            'data' => $mapped->concat($exams)->sortBy('starts_at')->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'event_type' => ['required', 'in:holiday,event,exam'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'all_day' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'exam_id' => ['nullable', 'exists:exams,id'],
        ]);

        $data['created_by'] = $request->user()->id;
        $event = CalendarEvent::query()->create($data);

        return response()->json(['data' => $event->load(['schoolClass', 'exam'])], 201);
    }

    public function update(Request $request, CalendarEvent $calendarEvent)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:200'],
            'event_type' => ['sometimes', 'in:holiday,event,exam'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['nullable', 'date'],
            'all_day' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
        ]);

        $calendarEvent->update($data);

        return response()->json(['data' => $calendarEvent->fresh(['schoolClass', 'exam'])]);
    }

    public function destroy(CalendarEvent $calendarEvent)
    {
        $calendarEvent->delete();

        return response()->json(['message' => 'Event removed.']);
    }
}
