<?php

namespace App\Services;

use App\Mail\SchoolAlertMail;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\NotificationLog;
use App\Models\ParentGuardian;
use App\Models\StudentFee;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SchoolNotificationService
{
    public function sendEmail(string $to, string $subject, string $body, string $type, User $actor, array $meta = []): NotificationLog
    {
        $log = NotificationLog::query()->create([
            'channel' => 'email',
            'notification_type' => $type,
            'recipient_email' => $to,
            'subject' => $subject,
            'body' => $body,
            'status' => 'queued',
            'meta' => $meta,
            'created_by' => $actor->id,
        ]);

        try {
            $mailable = new SchoolAlertMail($subject, $body);
            if (config('queue.default') !== 'sync') {
                Mail::to($to)->queue($mailable);
            } else {
                Mail::to($to)->send($mailable);
                $log->update(['status' => 'sent', 'sent_at' => now()]);
            }
        } catch (\Throwable $e) {
            Log::warning('Email notification failed: '.$e->getMessage());
            $log->update(['status' => 'failed', 'meta' => array_merge($meta, ['error' => $e->getMessage()])]);
        }

        return $log->fresh();
    }

    public function sendSms(string $phone, string $body, string $type, User $actor, array $meta = []): NotificationLog
    {
        return NotificationLog::query()->create([
            'channel' => 'sms',
            'notification_type' => $type,
            'recipient_phone' => $phone,
            'subject' => null,
            'body' => $body,
            'status' => 'sent',
            'sent_at' => now(),
            'meta' => array_merge($meta, ['simulated' => true]),
            'created_by' => $actor->id,
        ]);
    }

    /**
     * @return list<NotificationLog>
     */
    public function sendFeeReminders(User $actor, bool $includeSms = false): array
    {
        $logs = [];
        $fees = StudentFee::query()
            ->with(['student.parents'])
            ->whereIn('status', ['pending', 'partial'])
            ->get();

        foreach ($fees as $fee) {
            $due = $fee->balanceDue();
            foreach ($fee->student->parents as $parent) {
                $email = $parent->email ?: $parent->user?->email;
                if ($email) {
                    $subject = 'Fee reminder: '.$fee->title;
                    $body = "Dear {$parent->full_name},\n\nThis is a reminder that {$fee->student->full_name} has an outstanding balance of {$due} on \"{$fee->title}\".\n\nThank you,\nSchool Office";
                    $logs[] = $this->sendEmail($email, $subject, $body, 'fee_reminder', $actor, [
                        'student_fee_id' => $fee->id,
                        'parent_id' => $parent->id,
                    ]);
                }
                if ($includeSms && $parent->phone) {
                    $logs[] = $this->sendSms(
                        $parent->phone,
                        "Fee reminder: {$fee->student->full_name} owes {$due} on {$fee->title}.",
                        'fee_reminder',
                        $actor,
                        ['student_fee_id' => $fee->id]
                    );
                }
            }
        }

        return $logs;
    }

    /**
     * @return list<NotificationLog>
     */
    public function sendAbsenceAlerts(User $actor, string $date, bool $includeSms = false): array
    {
        $logs = [];
        $records = Attendance::query()
            ->with(['student.parents'])
            ->whereDate('attendance_date', $date)
            ->where('status', 'absent')
            ->get();

        foreach ($records as $row) {
            foreach ($row->student->parents as $parent) {
                $email = $parent->email ?: $parent->user?->email;
                if ($email) {
                    $subject = 'Absence alert — '.$row->student->full_name;
                    $body = "Dear {$parent->full_name},\n\n{$row->student->full_name} was marked absent on {$date}.\n\nIf this is unexpected, please contact the school.\n\nRegards,\nSchool Office";
                    $logs[] = $this->sendEmail($email, $subject, $body, 'absence_alert', $actor, [
                        'attendance_id' => $row->id,
                        'parent_id' => $parent->id,
                    ]);
                }
                if ($includeSms && $parent->phone) {
                    $logs[] = $this->sendSms(
                        $parent->phone,
                        "Absence alert: {$row->student->full_name} absent on {$date}.",
                        'absence_alert',
                        $actor,
                        ['attendance_id' => $row->id]
                    );
                }
            }
        }

        return $logs;
    }

    /**
     * @return list<NotificationLog>
     */
    public function sendAnnouncementNotice(User $actor, int $announcementId, bool $includeSms = false): array
    {
        $announcement = Announcement::query()->findOrFail($announcementId);
        $logs = [];

        $parentsQuery = ParentGuardian::query()->with('user');
        if ($announcement->school_class_id) {
            $parentsQuery->whereHas('students', fn ($q) => $q->where('school_class_id', $announcement->school_class_id));
        }
        $parents = $parentsQuery->get();

        foreach ($parents as $parent) {
            $email = $parent->email ?: $parent->user?->email;
            if ($email) {
                $subject = 'School notice: '.$announcement->title;
                $body = "Dear {$parent->full_name},\n\n{$announcement->title}\n\n{$announcement->body}\n\n— School Administration";
                $logs[] = $this->sendEmail($email, $subject, $body, 'notice', $actor, [
                    'announcement_id' => $announcement->id,
                    'parent_id' => $parent->id,
                ]);
            }
            if ($includeSms && $parent->phone) {
                $logs[] = $this->sendSms(
                    $parent->phone,
                    "Notice: {$announcement->title}",
                    'notice',
                    $actor,
                    ['announcement_id' => $announcement->id]
                );
            }
        }

        return $logs;
    }
}
