<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 10);
            $table->string('notification_type', 40);
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone', 20)->nullable();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('status', 20)->default('sent');
            $table->json('meta')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('message_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->string('subject');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->string('moderation_status', 20)->default('pending');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->boolean('visible_to_parent')->default(false);
            $table->timestamps();
        });

        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('event_type', 20)->default('event');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->boolean('all_day')->default(false);
            $table->text('description')->nullable();
            $table->foreignId('school_class_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('exam_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('message_threads');
        Schema::dropIfExists('notification_logs');
    }
};
