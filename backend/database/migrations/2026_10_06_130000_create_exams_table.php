<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 30)->unique();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('exam_type', ['quiz', 'unit_test', 'midterm', 'final', 'practical']);
            $table->date('exam_date');
            $table->decimal('max_marks', 6, 2)->default(100);
            $table->string('academic_year', 9);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['school_class_id', 'exam_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
