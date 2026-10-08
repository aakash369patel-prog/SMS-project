<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->enum('audience', ['all', 'parents', 'teachers', 'students', 'staff'])->default('all');
            $table->foreignId('school_class_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_published', 'audience']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
