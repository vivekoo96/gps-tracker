<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SATs (Sanitation Workers) Table
        Schema::create('sats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('employee_id')->unique();
            $table->string('phone', 20)->nullable();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
            $table->foreignId('ward_id')->nullable()->constrained('wards')->onDelete('set null');
            $table->foreignId('assigned_vehicle_id')->nullable()->constrained('devices')->onDelete('set null');
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->timestamps();
        });

        // SAT Attendance Table
        Schema::create('sat_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sat_id')->constrained('sats')->onDelete('cascade');
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->enum('status', ['present', 'absent', 'half_day', 'leave'])->default('absent');
            $table->foreignId('vehicle_id')->nullable()->constrained('devices')->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['sat_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sat_attendance');
        Schema::dropIfExists('sats');
    }
};
