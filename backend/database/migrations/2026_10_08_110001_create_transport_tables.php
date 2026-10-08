<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 30)->unique();
            $table->string('start_point')->nullable();
            $table->string('end_point')->nullable();
            $table->time('morning_departure')->nullable();
            $table->time('afternoon_departure')->nullable();
            $table->timestamps();
        });

        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_route_id')->nullable()->constrained()->nullOnDelete();
            $table->string('registration_number', 30)->unique();
            $table->unsignedSmallInteger('capacity')->default(40);
            $table->string('driver_name')->nullable();
            $table->string('driver_phone', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('student_transport_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transport_route_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bus_id')->nullable()->constrained()->nullOnDelete();
            $table->string('pickup_point')->nullable();
            $table->string('drop_point')->nullable();
            $table->enum('trip_type', ['pickup', 'drop', 'both'])->default('both');
            $table->timestamps();

            $table->unique(['student_id', 'transport_route_id'], 'sta_student_route_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_transport_assignments');
        Schema::dropIfExists('buses');
        Schema::dropIfExists('transport_routes');
    }
};
