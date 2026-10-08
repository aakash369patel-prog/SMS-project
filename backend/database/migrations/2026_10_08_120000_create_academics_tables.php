<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->date('due_date');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->text('content')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('marks', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'student_id'], 'asgn_sub_student_unique');
        });

        Schema::create('online_quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->decimal('max_score', 8, 2)->default(100);
            $table->timestamps();
        });

        Schema::create('online_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_quiz_id')->constrained()->cascadeOnDelete();
            $table->text('question_text');
            $table->string('question_type', 10)->default('mcq');
            $table->json('options')->nullable();
            $table->string('correct_answer', 50)->nullable();
            $table->decimal('marks', 8, 2)->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('online_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->string('status', 20)->default('in_progress');
            $table->timestamps();

            $table->unique(['online_quiz_id', 'student_id'], 'quiz_attempt_student_unique');
        });

        Schema::create('online_quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('online_quiz_question_id')->constrained()->cascadeOnDelete();
            $table->text('answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('marks_awarded', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['online_quiz_attempt_id', 'online_quiz_question_id'], 'quiz_ans_q_unique');
        });

        Schema::create('syllabi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('academic_year', 20);
            $table->string('title');
            $table->text('overview')->nullable();
            $table->json('units')->nullable();
            $table->timestamps();

            $table->unique(['school_class_id', 'subject_id', 'academic_year'], 'syllabus_cls_sub_yr_unique');
        });

        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->date('planned_date')->nullable();
            $table->text('objectives')->nullable();
            $table->text('content')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();
        });

        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('to_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->string('academic_year', 20);
            $table->timestamp('promoted_at');
            $table->foreignId('promoted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_promotions');
        Schema::dropIfExists('lesson_plans');
        Schema::dropIfExists('syllabi');
        Schema::dropIfExists('online_quiz_answers');
        Schema::dropIfExists('online_quiz_attempts');
        Schema::dropIfExists('online_quiz_questions');
        Schema::dropIfExists('online_quizzes');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
    }
};
