<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('employee_code', 30)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->string('department', 80)->nullable();
            $table->string('job_title', 80)->nullable();
            $table->date('hire_date')->nullable();
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_member_id')->constrained()->cascadeOnDelete();
            $table->enum('leave_type', ['sick', 'casual', 'annual', 'unpaid', 'other']);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_member_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('allowances', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('net_pay', 10, 2);
            $table->enum('status', ['draft', 'paid'])->default('draft');
            $table->date('paid_date')->nullable();
            $table->timestamps();

            $table->unique(['staff_member_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_entries');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('staff_members');
    }
};
