<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']);
            $table->unsignedTinyInteger('period_number');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('room', 50)->nullable();
            $table->timestamps();

            $table->unique(['school_class_id', 'day_of_week', 'period_number'], 'timetable_class_day_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_slots');
    }
};
