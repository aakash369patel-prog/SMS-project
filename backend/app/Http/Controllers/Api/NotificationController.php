<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Services\SchoolNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = NotificationLog::query()->with('creator')->latest();

        if ($request->filled('notification_type')) {
            $query->where('notification_type', $request->string('notification_type'));
        }

        return response()->json($query->paginate(30));
    }

    public function sendFeeReminders(Request $request, SchoolNotificationService $service)
    {
        $data = $request->validate(['include_sms' => ['sometimes', 'boolean']]);
        $logs = $service->sendFeeReminders($request->user(), ! empty($data['include_sms']));

        return response()->json([
            'message' => count($logs).' notification(s) processed.',
            'data' => $logs,
        ]);
    }

    public function sendAbsenceAlerts(Request $request, SchoolNotificationService $service)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'include_sms' => ['sometimes', 'boolean'],
        ]);
        $logs = $service->sendAbsenceAlerts($request->user(), $data['date'], ! empty($data['include_sms']));

        return response()->json([
            'message' => count($logs).' notification(s) processed.',
            'data' => $logs,
        ]);
    }

    public function sendAnnouncementNotice(Request $request, SchoolNotificationService $service)
    {
        $data = $request->validate([
            'announcement_id' => ['required', 'exists:announcements,id'],
            'include_sms' => ['sometimes', 'boolean'],
        ]);
        $logs = $service->sendAnnouncementNotice($request->user(), (int) $data['announcement_id'], ! empty($data['include_sms']));

        return response()->json([
            'message' => count($logs).' notification(s) processed.',
            'data' => $logs,
        ]);
    }
}
