<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\CalendarEvent;
use App\Models\LessonPlan;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\OnlineQuiz;
use App\Models\OnlineQuizQuestion;
use App\Models\Syllabus;
use App\Models\Book;
use App\Models\Bus;
use App\Models\StaffMember;
use App\Models\StudentTransportAssignment;
use App\Models\TransportRoute;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamGrade;
use App\Models\Expense;
use App\Models\FeeType;
use App\Models\ParentGuardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableSlot;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SchoolManagementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@school.local'],
            [
                'name' => 'School Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $parentUser = User::query()->updateOrCreate(
            ['email' => 'parent@school.local'],
            [
                'name' => 'Robert Doe',
                'password' => Hash::make('password'),
                'role' => 'parent',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'staff@school.local'],
            [
                'name' => 'Office Staff',
                'password' => Hash::make('password'),
                'role' => 'staff',
            ]
        );

        $teacherUser = User::query()->updateOrCreate(
            ['email' => 'teacher@school.local'],
            [
                'name' => 'Jane Smith',
                'password' => Hash::make('password'),
                'role' => 'teacher',
            ]
        );

        $teacher = Teacher::query()->updateOrCreate(
            ['employee_code' => 'TCH-001'],
            [
                'user_id' => $teacherUser->id,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@school.local',
                'department' => 'Science',
                'hire_date' => now()->subYears(2)->toDateString(),
            ]
        );

        $schoolClass = SchoolClass::query()->updateOrCreate(
            [
                'name' => 'Grade 10',
                'section' => 'A',
                'academic_year' => '2025-2026',
            ],
            [
                'capacity' => 40,
                'class_teacher_id' => $teacher->id,
            ]
        );

        $student = Student::query()->updateOrCreate(
            ['admission_number' => 'STU-1001'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@school.local',
                'gender' => 'male',
                'school_class_id' => $schoolClass->id,
            ]
        );

        $subject = Subject::query()->updateOrCreate(
            ['code' => 'MATH-10A'],
            [
                'name' => 'Mathematics',
                'school_class_id' => $schoolClass->id,
                'teacher_id' => $teacher->id,
            ]
        );

        $exam = Exam::query()->updateOrCreate(
            ['code' => 'EXAM-MATH-MID'],
            [
                'name' => 'Mathematics Midterm',
                'school_class_id' => $schoolClass->id,
                'subject_id' => $subject->id,
                'exam_type' => 'midterm',
                'exam_date' => now()->subDays(7)->toDateString(),
                'max_marks' => 100,
                'academic_year' => '2025-2026',
            ]
        );

        ExamGrade::query()->updateOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $student->id,
            ],
            [
                'marks_obtained' => 82,
                'recorded_by' => $admin->id,
            ]
        );

        Attendance::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'attendance_date' => now()->toDateString(),
            ],
            [
                'school_class_id' => $schoolClass->id,
                'status' => 'present',
                'recorded_by' => $admin->id,
            ]
        );

        TimetableSlot::query()->updateOrCreate(
            [
                'school_class_id' => $schoolClass->id,
                'day_of_week' => 'monday',
                'period_number' => 1,
            ],
            [
                'start_time' => '08:00',
                'end_time' => '08:45',
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'room' => 'Room 101',
            ]
        );

        TimetableSlot::query()->updateOrCreate(
            [
                'school_class_id' => $schoolClass->id,
                'day_of_week' => 'wednesday',
                'period_number' => 3,
            ],
            [
                'start_time' => '09:50',
                'end_time' => '10:35',
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'room' => 'Lab 2',
            ]
        );

        $parent = ParentGuardian::query()->updateOrCreate(
            ['email' => 'parent@school.local'],
            [
                'user_id' => $parentUser->id,
                'first_name' => 'Robert',
                'last_name' => 'Doe',
                'phone' => '+1-555-0100',
                'address' => '123 School Street',
            ]
        );
        $parent->students()->sync([
            $student->id => ['relation' => 'father'],
        ]);

        $feeType = FeeType::query()->updateOrCreate(
            ['code' => 'TUITION-2025'],
            [
                'name' => 'Annual Tuition',
                'amount' => 1200,
                'academic_year' => '2025-2026',
                'description' => 'Tuition fee for academic year 2025-2026',
            ]
        );

        $studentFee = StudentFee::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'fee_type_id' => $feeType->id,
                'academic_year' => '2025-2026',
            ],
            [
                'title' => 'Annual Tuition',
                'amount' => 1200,
                'discount_amount' => 200,
                'discount_type' => 'scholarship',
                'discount_note' => 'Merit scholarship 2025-2026',
                'due_date' => now()->addMonths(1)->toDateString(),
            ]
        );
        if ((float) $studentFee->amount_paid === 0.0) {
            \App\Models\FeePayment::query()->updateOrCreate(
                ['student_fee_id' => $studentFee->id, 'reference' => 'SEED-OPENING'],
                [
                    'amount' => 400,
                    'payment_date' => now()->subMonth()->toDateString(),
                    'payment_method' => 'bank',
                    'recorded_by' => $admin->id,
                ]
            );
        }
        $studentFee->refreshPaymentTotals();

        Expense::query()->updateOrCreate(
            [
                'title' => 'Science lab supplies',
                'expense_date' => now()->subDays(3)->toDateString(),
            ],
            [
                'category' => 'Supplies',
                'amount' => 450,
                'paid_to' => 'EduMart Supplies',
                'payment_method' => 'bank',
                'notes' => 'Quarterly lab consumables',
                'recorded_by' => $admin->id,
            ]
        );

        Announcement::query()->updateOrCreate(
            ['title' => 'Parent-Teacher Meeting'],
            [
                'body' => 'PTM is scheduled for next Friday at 10:00 AM in the main hall.',
                'audience' => 'parents',
                'school_class_id' => $schoolClass->id,
                'is_published' => true,
                'published_at' => now(),
                'created_by' => $admin->id,
            ]
        );

        Book::query()->updateOrCreate(
            ['isbn' => '978-0143127741'],
            [
                'title' => 'Introduction to Physics',
                'author' => 'H. Smith',
                'category' => 'Science',
                'total_copies' => 5,
                'available_copies' => 5,
            ]
        );

        $route = TransportRoute::query()->updateOrCreate(
            ['code' => 'RT-NORTH'],
            [
                'name' => 'North City Route',
                'start_point' => 'Main Gate',
                'end_point' => 'North Terminal',
                'morning_departure' => '07:30',
                'afternoon_departure' => '15:30',
            ]
        );

        $bus = Bus::query()->updateOrCreate(
            ['registration_number' => 'SCH-BUS-01'],
            [
                'transport_route_id' => $route->id,
                'capacity' => 45,
                'driver_name' => 'Mike Johnson',
                'driver_phone' => '+1-555-0200',
            ]
        );

        StudentTransportAssignment::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'transport_route_id' => $route->id,
            ],
            [
                'bus_id' => $bus->id,
                'pickup_point' => 'Oak Street Stop',
                'drop_point' => 'Main Gate',
                'trip_type' => 'both',
            ]
        );

        StaffMember::query()->updateOrCreate(
            ['employee_code' => 'STF-001'],
            [
                'first_name' => 'Emily',
                'last_name' => 'Clark',
                'email' => 'emily.clark@school.local',
                'department' => 'Administration',
                'job_title' => 'Office Assistant',
                'hire_date' => now()->subYear()->toDateString(),
                'basic_salary' => 2800,
                'status' => 'active',
            ]
        );

        SchoolClass::query()->updateOrCreate(
            [
                'name' => 'Grade 11',
                'section' => 'A',
                'academic_year' => '2025-2026',
            ],
            [
                'capacity' => 40,
                'class_teacher_id' => $teacher->id,
            ]
        );

        $assignment = Assignment::query()->updateOrCreate(
            [
                'title' => 'Algebra Problem Set 3',
                'school_class_id' => $schoolClass->id,
            ],
            [
                'description' => 'Complete exercises 1–10 from chapter 4.',
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'due_date' => now()->addDays(5)->toDateString(),
                'published_at' => now(),
            ]
        );

        AssignmentSubmission::query()->updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ],
            [
                'status' => 'pending',
            ]
        );

        $quiz = OnlineQuiz::query()->updateOrCreate(
            [
                'title' => 'Math Quick Quiz — Fractions',
                'school_class_id' => $schoolClass->id,
            ],
            [
                'subject_id' => $subject->id,
                'duration_minutes' => 15,
                'starts_at' => now()->subHour(),
                'ends_at' => now()->addWeek(),
                'is_published' => true,
                'max_score' => 2,
            ]
        );

        OnlineQuizQuestion::query()->updateOrCreate(
            [
                'online_quiz_id' => $quiz->id,
                'sort_order' => 0,
            ],
            [
                'question_text' => 'What is 1/2 + 1/4?',
                'question_type' => 'mcq',
                'options' => ['1/4', '3/4', '1', '2/3'],
                'correct_answer' => '3/4',
                'marks' => 1,
            ]
        );

        OnlineQuizQuestion::query()->updateOrCreate(
            [
                'online_quiz_id' => $quiz->id,
                'sort_order' => 1,
            ],
            [
                'question_text' => 'Which is greater: 0.5 or 1/3?',
                'question_type' => 'mcq',
                'options' => ['0.5', '1/3', 'Equal'],
                'correct_answer' => '0.5',
                'marks' => 1,
            ]
        );

        $syllabus = Syllabus::query()->updateOrCreate(
            [
                'school_class_id' => $schoolClass->id,
                'subject_id' => $subject->id,
                'academic_year' => '2025-2026',
            ],
            [
                'title' => 'Grade 10 Mathematics Syllabus',
                'overview' => 'Algebra, geometry, and introductory statistics.',
                'units' => [
                    ['name' => 'Algebra', 'weeks' => 8],
                    ['name' => 'Geometry', 'weeks' => 6],
                ],
            ]
        );

        LessonPlan::query()->updateOrCreate(
            [
                'title' => 'Linear equations intro',
                'school_class_id' => $schoolClass->id,
                'subject_id' => $subject->id,
            ],
            [
                'syllabus_id' => $syllabus->id,
                'teacher_id' => $teacher->id,
                'planned_date' => now()->addDays(2)->toDateString(),
                'objectives' => 'Students solve one-variable linear equations.',
                'content' => 'Warm-up, guided practice, exit ticket.',
                'status' => 'published',
            ]
        );

        CalendarEvent::query()->updateOrCreate(
            [
                'title' => 'Diwali holiday',
                'starts_at' => now()->addMonths(1)->startOfDay(),
            ],
            [
                'event_type' => 'holiday',
                'ends_at' => now()->addMonths(1)->addDay()->endOfDay(),
                'all_day' => true,
                'description' => 'School closed.',
                'created_by' => $admin->id,
            ]
        );

        $thread = MessageThread::query()->updateOrCreate(
            [
                'parent_id' => $parent->id,
                'subject' => 'John\'s progress in Mathematics',
            ],
            [
                'created_by' => $teacherUser->id,
            ]
        );

        Message::query()->updateOrCreate(
            [
                'message_thread_id' => $thread->id,
                'sender_user_id' => $teacherUser->id,
                'body' => 'John is doing well in recent tests. Please encourage daily practice.',
            ],
            [
                'moderation_status' => 'pending',
                'visible_to_parent' => false,
            ]
        );

        Message::query()->updateOrCreate(
            [
                'message_thread_id' => $thread->id,
                'sender_user_id' => $admin->id,
                'body' => 'Welcome to the parent messaging portal. Approved messages from teachers appear here after review.',
            ],
            [
                'moderation_status' => 'approved',
                'visible_to_parent' => true,
                'moderated_by' => $admin->id,
                'moderated_at' => now(),
            ]
        );
    }
}
