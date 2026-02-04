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
        Schema::create('fuel_sensors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $table->decimal('tank_capacity', 8, 2)->comment('Capacity in liters');
            $table->decimal('current_level', 8, 2)->default(0)->comment('Current fuel in liters');
            $table->json('calibration_data')->nullable()->comment('Sensor value to liters mapping');
            $table->string('status')->default('active'); // active, inactive, calibration
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_sensors');
    }
};
