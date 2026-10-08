<?php

use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BookLoanController;
use App\Http\Controllers\Api\BusController;
use App\Http\Controllers\Api\CalendarEventController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\OnlinePaymentController;
use App\Http\Controllers\Api\LessonPlanController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\MessageThreadController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OnlineQuizController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\SyllabusController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StaffMemberController;
use App\Http\Controllers\Api\StudentTransportController;
use App\Http\Controllers\Api\TransportRouteController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\FeeTypeController;
use App\Http\Controllers\Api\ParentGuardianController;
use App\Http\Controllers\Api\ParentPortalController;
use App\Http\Controllers\Api\SchoolClassController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentFileController;
use App\Http\Controllers\Api\StudentFeeController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\TimetableController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::middleware('parent')->prefix('parent')->group(function (): void {
            Route::get('/overview', [ParentPortalController::class, 'overview']);
            Route::get('/fees', [ParentPortalController::class, 'fees']);
            Route::get('/announcements', [ParentPortalController::class, 'announcements']);
            Route::get('/messages', [ParentPortalController::class, 'messages']);
            Route::get('/messages/{messageThread}', [ParentPortalController::class, 'messageThread']);
            Route::post('/messages/{messageThread}/reply', [ParentPortalController::class, 'replyMessage']);
            Route::get('/calendar', [ParentPortalController::class, 'calendar']);
            Route::get('/notifications', [ParentPortalController::class, 'notificationHistory']);
            Route::post('/fees/{studentFee}/payment-intent', [OnlinePaymentController::class, 'createIntent']);
            Route::post('/payment-orders/{onlinePaymentOrder}/confirm', [OnlinePaymentController::class, 'confirm']);
        });

        Route::middleware('staff')->group(function (): void {
            Route::get('/announcements/feed', [AnnouncementController::class, 'feed'])
                ->middleware('permission:announcements.feed');

            Route::get('/dashboard', DashboardController::class)
                ->middleware('permission:dashboard.view');

            Route::middleware('permission:teachers.view')->group(function (): void {
                Route::get('teachers', [TeacherController::class, 'index']);
                Route::get('teachers/{teacher}', [TeacherController::class, 'show']);
            });
            Route::middleware('permission:teachers.manage')->group(function (): void {
                Route::post('teachers', [TeacherController::class, 'store']);
                Route::put('teachers/{teacher}', [TeacherController::class, 'update']);
                Route::patch('teachers/{teacher}', [TeacherController::class, 'update']);
                Route::delete('teachers/{teacher}', [TeacherController::class, 'destroy']);
            });

            Route::middleware('permission:students.view')->group(function (): void {
                Route::get('students', [StudentController::class, 'index']);
                Route::get('students/{student}', [StudentController::class, 'show']);
            });
            Route::middleware('permission:students.manage')->group(function (): void {
                Route::post('students', [StudentController::class, 'store']);
                Route::put('students/{student}', [StudentController::class, 'update']);
                Route::patch('students/{student}', [StudentController::class, 'update']);
                Route::delete('students/{student}', [StudentController::class, 'destroy']);
            });
            Route::middleware('permission:files.upload')->group(function (): void {
                Route::post('students/{student}/photo', [StudentFileController::class, 'uploadPhoto']);
                Route::get('students/{student}/documents', [StudentFileController::class, 'listDocuments']);
                Route::post('students/{student}/documents', [StudentFileController::class, 'uploadDocument']);
                Route::delete('students/{student}/documents/{document}', [StudentFileController::class, 'deleteDocument']);
            });

            Route::middleware('permission:classes.view')->group(function (): void {
                Route::get('classes', [SchoolClassController::class, 'index']);
                Route::get('classes/{schoolClass}', [SchoolClassController::class, 'show']);
            });
            Route::middleware('permission:classes.manage')->group(function (): void {
                Route::post('classes', [SchoolClassController::class, 'store']);
                Route::put('classes/{schoolClass}', [SchoolClassController::class, 'update']);
                Route::patch('classes/{schoolClass}', [SchoolClassController::class, 'update']);
                Route::delete('classes/{schoolClass}', [SchoolClassController::class, 'destroy']);
            });

            Route::middleware('permission:subjects.view')->group(function (): void {
                Route::get('subjects', [SubjectController::class, 'index']);
                Route::get('subjects/{subject}', [SubjectController::class, 'show']);
            });
            Route::middleware('permission:subjects.manage')->group(function (): void {
                Route::post('subjects', [SubjectController::class, 'store']);
                Route::put('subjects/{subject}', [SubjectController::class, 'update']);
                Route::patch('subjects/{subject}', [SubjectController::class, 'update']);
                Route::delete('subjects/{subject}', [SubjectController::class, 'destroy']);
            });

            Route::middleware('permission:attendance.view')->group(function (): void {
                Route::get('attendance/sheet', [AttendanceController::class, 'sheet']);
                Route::get('attendance/summary', [AttendanceController::class, 'summary']);
                Route::get('attendance', [AttendanceController::class, 'index']);
            });
            Route::middleware('permission:attendance.manage')->group(function (): void {
                Route::post('attendance/bulk', [AttendanceController::class, 'bulkStore']);
            });

            Route::middleware('permission:exams.view')->group(function (): void {
                Route::get('exams', [ExamController::class, 'index']);
                Route::get('exams/{exam}', [ExamController::class, 'show']);
                Route::get('exams/{exam}/sheet', [ExamController::class, 'sheet']);
                Route::get('exams/{exam}/summary', [ExamController::class, 'summary']);
            });
            Route::middleware('permission:exams.manage')->group(function (): void {
                Route::post('exams', [ExamController::class, 'store']);
                Route::put('exams/{exam}', [ExamController::class, 'update']);
                Route::patch('exams/{exam}', [ExamController::class, 'update']);
                Route::delete('exams/{exam}', [ExamController::class, 'destroy']);
            });
            Route::middleware('permission:grades.manage')->group(function (): void {
                Route::post('exams/{exam}/grades/bulk', [ExamController::class, 'bulkGrades']);
            });

            Route::middleware('permission:timetable.view')->group(function (): void {
                Route::get('timetable/grid', [TimetableController::class, 'grid']);
                Route::get('timetable', [TimetableController::class, 'index']);
            });
            Route::middleware('permission:timetable.manage')->group(function (): void {
                Route::post('timetable/bulk', [TimetableController::class, 'bulkStore']);
            });

            Route::middleware('permission:parents.view')->group(function (): void {
                Route::get('parents', [ParentGuardianController::class, 'index']);
                Route::get('parents/{parentGuardian}', [ParentGuardianController::class, 'show']);
            });
            Route::middleware('permission:parents.manage')->group(function (): void {
                Route::post('parents', [ParentGuardianController::class, 'store']);
                Route::put('parents/{parentGuardian}', [ParentGuardianController::class, 'update']);
                Route::patch('parents/{parentGuardian}', [ParentGuardianController::class, 'update']);
                Route::delete('parents/{parentGuardian}', [ParentGuardianController::class, 'destroy']);
            });

            Route::middleware('permission:fee_types.manage')->group(function (): void {
                Route::apiResource('fee-types', FeeTypeController::class);
            });

            Route::middleware('permission:fees.view')->group(function (): void {
                Route::get('student-fees', [StudentFeeController::class, 'index']);
                Route::get('student-fees/{studentFee}', [StudentFeeController::class, 'show']);
            });
            Route::middleware('permission:fees.manage')->group(function (): void {
                Route::post('student-fees/bulk-class', [StudentFeeController::class, 'bulkAssignClass']);
                Route::post('student-fees', [StudentFeeController::class, 'store']);
                Route::delete('student-fees/{studentFee}', [StudentFeeController::class, 'destroy']);
                Route::patch('student-fees/{studentFee}/discount', [StudentFeeController::class, 'applyDiscount']);
            });
            Route::middleware('permission:payments.record')->group(function (): void {
                Route::post('student-fees/{studentFee}/payments', [StudentFeeController::class, 'recordPayment']);
            });

            Route::middleware('permission:announcements.view')->group(function (): void {
                Route::get('announcements', [AnnouncementController::class, 'index']);
                Route::get('announcements/{announcement}', [AnnouncementController::class, 'show']);
            });
            Route::middleware('permission:announcements.manage')->group(function (): void {
                Route::post('announcements', [AnnouncementController::class, 'store']);
                Route::put('announcements/{announcement}', [AnnouncementController::class, 'update']);
                Route::patch('announcements/{announcement}', [AnnouncementController::class, 'update']);
                Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy']);
                Route::post('announcements/{announcement}/attachment', [AnnouncementController::class, 'uploadAttachment']);
            });

            Route::middleware('permission:reports.view')->group(function (): void {
                Route::get('reports/report-card/pdf', [ReportController::class, 'reportCardPdf']);
                Route::get('reports/attendance-sheet/pdf', [ReportController::class, 'attendanceSheetPdf']);
                Route::get('reports/fee-receipt/pdf', [ReportController::class, 'feeReceiptPdf']);
            });
            Route::middleware('permission:exports.download')->group(function (): void {
                Route::get('exports/students', [ExportController::class, 'students']);
                Route::get('exports/attendance', [ExportController::class, 'attendance']);
                Route::get('exports/fees', [ExportController::class, 'fees']);
            });

            Route::middleware('permission:library.view')->group(function (): void {
                Route::get('books', [BookController::class, 'index']);
                Route::get('book-loans', [BookLoanController::class, 'index']);
            });
            Route::middleware('permission:library.manage')->group(function (): void {
                Route::post('books', [BookController::class, 'store']);
                Route::put('books/{book}', [BookController::class, 'update']);
                Route::delete('books/{book}', [BookController::class, 'destroy']);
                Route::post('book-loans/issue', [BookLoanController::class, 'issue']);
                Route::post('book-loans/{bookLoan}/return', [BookLoanController::class, 'returnBook']);
            });

            Route::middleware('permission:transport.manage')->group(function (): void {
                Route::get('transport/routes', [TransportRouteController::class, 'index']);
                Route::post('transport/routes', [TransportRouteController::class, 'store']);
                Route::put('transport/routes/{transportRoute}', [TransportRouteController::class, 'update']);
                Route::delete('transport/routes/{transportRoute}', [TransportRouteController::class, 'destroy']);
                Route::get('transport/buses', [BusController::class, 'index']);
                Route::post('transport/buses', [BusController::class, 'store']);
                Route::put('transport/buses/{bus}', [BusController::class, 'update']);
                Route::delete('transport/buses/{bus}', [BusController::class, 'destroy']);
                Route::get('transport/assignments', [StudentTransportController::class, 'index']);
                Route::post('transport/assignments', [StudentTransportController::class, 'store']);
                Route::delete('transport/assignments/{studentTransport}', [StudentTransportController::class, 'destroy']);
            });

            Route::middleware('permission:hr.view')->group(function (): void {
                Route::get('hr/staff', [StaffMemberController::class, 'index']);
                Route::get('hr/leave', [LeaveRequestController::class, 'index']);
                Route::get('hr/payroll', [PayrollController::class, 'index']);
            });
            Route::middleware('permission:hr.manage')->group(function (): void {
                Route::post('hr/staff', [StaffMemberController::class, 'store']);
                Route::put('hr/staff/{staffMember}', [StaffMemberController::class, 'update']);
                Route::delete('hr/staff/{staffMember}', [StaffMemberController::class, 'destroy']);
                Route::post('hr/leave', [LeaveRequestController::class, 'store']);
                Route::patch('hr/leave/{leaveRequest}', [LeaveRequestController::class, 'update']);
                Route::post('hr/payroll', [PayrollController::class, 'store']);
            });

            Route::middleware('permission:academics.view')->group(function (): void {
                Route::get('academics/assignments', [AssignmentController::class, 'index']);
                Route::get('academics/assignments/{assignment}/submissions', [AssignmentController::class, 'submissions']);
                Route::get('academics/quizzes', [OnlineQuizController::class, 'index']);
                Route::get('academics/quizzes/{onlineQuiz}', [OnlineQuizController::class, 'show']);
                Route::get('academics/quizzes/{onlineQuiz}/attempts', [OnlineQuizController::class, 'attempts']);
                Route::get('academics/syllabi', [SyllabusController::class, 'index']);
                Route::get('academics/lesson-plans', [LessonPlanController::class, 'index']);
                Route::get('academics/promotions', [PromotionController::class, 'history']);
            });
            Route::middleware('permission:academics.manage')->group(function (): void {
                Route::post('academics/assignments', [AssignmentController::class, 'store']);
                Route::put('academics/assignments/{assignment}', [AssignmentController::class, 'update']);
                Route::delete('academics/assignments/{assignment}', [AssignmentController::class, 'destroy']);
                Route::patch('academics/assignments/{assignment}/submissions/{submission}', [AssignmentController::class, 'updateSubmission']);
                Route::post('academics/quizzes', [OnlineQuizController::class, 'store']);
                Route::put('academics/quizzes/{onlineQuiz}', [OnlineQuizController::class, 'update']);
                Route::delete('academics/quizzes/{onlineQuiz}', [OnlineQuizController::class, 'destroy']);
                Route::post('academics/quizzes/{onlineQuiz}/start', [OnlineQuizController::class, 'startAttempt']);
                Route::post('academics/quizzes/{onlineQuiz}/attempts/{attempt}/submit', [OnlineQuizController::class, 'submitAttempt']);
                Route::post('academics/syllabi', [SyllabusController::class, 'store']);
                Route::put('academics/syllabi/{syllabus}', [SyllabusController::class, 'update']);
                Route::delete('academics/syllabi/{syllabus}', [SyllabusController::class, 'destroy']);
                Route::post('academics/lesson-plans', [LessonPlanController::class, 'store']);
                Route::put('academics/lesson-plans/{lessonPlan}', [LessonPlanController::class, 'update']);
                Route::delete('academics/lesson-plans/{lessonPlan}', [LessonPlanController::class, 'destroy']);
            });
            Route::middleware('permission:promotion.manage')->group(function (): void {
                Route::post('academics/promotions/bulk', [PromotionController::class, 'bulkPromote']);
            });

            Route::middleware('permission:communication.view')->group(function (): void {
                Route::get('communication/notifications', [NotificationController::class, 'index']);
                Route::get('communication/threads', [MessageThreadController::class, 'index']);
                Route::get('communication/threads/{messageThread}', [MessageThreadController::class, 'show']);
                Route::get('communication/calendar', [CalendarEventController::class, 'index']);
            });
            Route::middleware('permission:communication.notify')->group(function (): void {
                Route::post('communication/notifications/fee-reminders', [NotificationController::class, 'sendFeeReminders']);
                Route::post('communication/notifications/absence-alerts', [NotificationController::class, 'sendAbsenceAlerts']);
                Route::post('communication/notifications/announcement', [NotificationController::class, 'sendAnnouncementNotice']);
            });
            Route::middleware('permission:communication.messages')->group(function (): void {
                Route::post('communication/threads', [MessageThreadController::class, 'store']);
                Route::post('communication/threads/{messageThread}/reply', [MessageThreadController::class, 'reply']);
            });
            Route::middleware('permission:communication.moderate')->group(function (): void {
                Route::patch('communication/messages/{message}/moderate', [MessageThreadController::class, 'moderate']);
            });
            Route::middleware('permission:communication.calendar')->group(function (): void {
                Route::post('communication/calendar/events', [CalendarEventController::class, 'store']);
                Route::put('communication/calendar/events/{calendarEvent}', [CalendarEventController::class, 'update']);
                Route::delete('communication/calendar/events/{calendarEvent}', [CalendarEventController::class, 'destroy']);
            });

            Route::middleware('permission:finance.payments.view')->group(function (): void {
                Route::get('finance/payment-orders', [OnlinePaymentController::class, 'index']);
            });
            Route::middleware('permission:audit.view')->group(function (): void {
                Route::get('audit-logs', [AuditLogController::class, 'index']);
            });

            Route::middleware('permission:finance.expenses')->group(function (): void {
                Route::get('finance/expenses', [ExpenseController::class, 'index']);
                Route::get('finance/expenses/summary', [ExpenseController::class, 'summary']);
                Route::post('finance/expenses', [ExpenseController::class, 'store']);
                Route::put('finance/expenses/{expense}', [ExpenseController::class, 'update']);
                Route::delete('finance/expenses/{expense}', [ExpenseController::class, 'destroy']);
            });
        });
    });
});
