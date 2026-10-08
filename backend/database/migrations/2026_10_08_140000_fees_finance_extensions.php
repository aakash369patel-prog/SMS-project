<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->decimal('discount_amount', 10, 2)->default(0)->after('amount');
            $table->string('discount_type', 30)->nullable()->after('discount_amount');
            $table->string('discount_note', 255)->nullable()->after('discount_type');
        });

        Schema::create('online_payment_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_fee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 20);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->string('status', 20)->default('created');
            $table->string('external_order_id', 100)->nullable();
            $table->string('external_payment_id', 100)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 60)->nullable();
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->string('paid_to', 150)->nullable();
            $table->string('payment_method', 20)->default('cash');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('online_payment_orders');
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'discount_type', 'discount_note']);
        });
    }
};
