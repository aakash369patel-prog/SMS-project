<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 30)->unique();
            $table->decimal('amount', 10, 2);
            $table->string('academic_year', 9);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('student_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_type_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->date('due_date');
            $table->enum('status', ['pending', 'partial', 'paid', 'cancelled'])->default('pending');
            $table->string('academic_year', 9);
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });

        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_fee_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'bank', 'online', 'cheque'])->default('cash');
            $table->string('reference', 100)->nullable();
            $table->string('notes', 255)->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('student_fees');
        Schema::dropIfExists('fee_types');
    }
};
