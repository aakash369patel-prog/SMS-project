<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('section', 10)->nullable();
            $table->string('academic_year', 9);
            $table->unsignedSmallInteger('capacity')->default(40);
            $table->foreignId('class_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->timestamps();
            $table->unique(['name', 'section', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
