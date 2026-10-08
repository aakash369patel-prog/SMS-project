<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('isbn', 30)->nullable()->unique();
            $table->string('author')->nullable();
            $table->string('category', 80)->nullable();
            $table->unsignedInteger('total_copies')->default(1);
            $table->unsignedInteger('available_copies')->default(1);
            $table->timestamps();
        });

        Schema::create('book_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('issued_date');
            $table->date('due_date');
            $table->date('returned_date')->nullable();
            $table->enum('status', ['issued', 'returned', 'overdue'])->default('issued');
            $table->decimal('fine_amount', 8, 2)->default(0);
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_loans');
        Schema::dropIfExists('books');
    }
};
