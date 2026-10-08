<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkTimetableRequest;
use App\Http\Resources\SchoolClassResource;
use App\Http\Resources\TimetableSlotResource;
use App\Models\SchoolClass;
use App\Models\TimetableSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TimetableController extends Controller
{
    public function grid(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'days' => ['nullable', 'array'],
            'days.*' => [Rule::in(TimetableSlot::DAYS)],
            'periods' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $schoolClass = SchoolClass::query()->findOrFail($data['school_class_id']);
        $days = $data['days'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $periodCount = $data['periods'] ?? TimetableSlot::PERIOD_COUNT;

        $slots = TimetableSlot::query()
            ->with(['subject', 'teacher'])
            ->where('school_class_id', $schoolClass->id)
            ->get()
            ->groupBy(fn (TimetableSlot $slot) => $slot->day_of_week.'-'.$slot->period_number);

        $grid = collect($days)->map(function (string $day) use ($periodCount, $slots) {
            $periods = [];
            for ($period = 1; $period <= $periodCount; $period++) {
                $key = $day.'-'.$period;
                $slot = $slots->get($key)?->first();
                $defaults = TimetableSlot::DEFAULT_PERIOD_TIMES[$period] ?? ['start' => null, 'end' => null];

                $periods[] = [
                    'period_number' => $period,
                    'default_start_time' => $defaults['start'],
                    'default_end_time' => $defaults['end'],
                    'slot' => $slot ? new TimetableSlotResource($slot) : null,
                ];
            }

            return [
                'day_of_week' => $day,
                'periods' => $periods,
            ];
        });

        return response()->json([
            'school_class' => new SchoolClassResource($schoolClass),
            'days' => $grid->values(),
        ]);
    }

    public function bulkStore(BulkTimetableRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $classId = (int) $data['school_class_id'];

        DB::transaction(function () use ($data, $classId): void {
            foreach ($data['slots'] as $slotData) {
                $hasAssignment = ! empty($slotData['subject_id'])
                    || ! empty($slotData['teacher_id'])
                    || ! empty($slotData['room']);

                $lookup = [
                    'school_class_id' => $classId,
                    'day_of_week' => $slotData['day_of_week'],
                    'period_number' => $slotData['period_number'],
                ];

                if (! $hasAssignment) {
                    TimetableSlot::query()->where($lookup)->delete();

                    continue;
                }

                $defaults = TimetableSlot::DEFAULT_PERIOD_TIMES[$slotData['period_number']] ?? ['start' => null, 'end' => null];

                TimetableSlot::query()->updateOrCreate($lookup, [
                    'start_time' => $slotData['start_time'] ?? $defaults['start'],
                    'end_time' => $slotData['end_time'] ?? $defaults['end'],
                    'subject_id' => $slotData['subject_id'] ?? null,
                    'teacher_id' => $slotData['teacher_id'] ?? null,
                    'room' => $slotData['room'] ?? null,
                ]);
            }
        });

        return response()->json(['message' => 'Timetable saved successfully.']);
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $filters = $request->validate([
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'day_of_week' => ['nullable', Rule::in(TimetableSlot::DAYS)],
        ]);

        $query = TimetableSlot::query()
            ->with(['schoolClass', 'subject', 'teacher'])
            ->orderBy('day_of_week')
            ->orderBy('period_number');

        if (! empty($filters['school_class_id'])) {
            $query->where('school_class_id', $filters['school_class_id']);
        }

        if (! empty($filters['day_of_week'])) {
            $query->where('day_of_week', $filters['day_of_week']);
        }

        return response()->json([
            'data' => TimetableSlotResource::collection($query->get()),
        ]);
    }
}
