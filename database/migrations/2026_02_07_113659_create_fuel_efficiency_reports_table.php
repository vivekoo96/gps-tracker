<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_efficiency_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('vehicle_id')->nullable(); // No FK constraint
            
            $table->enum('period', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->date('period_start');
            $table->date('period_end');
            
            $table->decimal('total_fuel_consumed', 10, 2)->default(0); // Liters
            $table->decimal('total_distance', 10, 2)->default(0); // km
            $table->decimal('average_efficiency', 8, 2)->nullable(); // km/L or L/100km
            
            $table->decimal('total_refuel_amount', 10, 2)->default(0); // Liters
            $table->decimal('total_refuel_cost', 10, 2)->default(0);
            $table->decimal('total_theft_amount', 10, 2)->default(0);
            $table->decimal('idle_fuel_consumed', 10, 2)->default(0);
            
            $table->integer('trips_count')->default(0);
            $table->timestamp('calculated_at');
            
            $table->timestamps();
            
            $table->unique(['vendor_id', 'device_id', 'period', 'period_start'], 'fuel_eff_vendor_device_period_unique');
            $table->index(['vendor_id', 'period', 'period_start']);
            $table->index(['device_id', 'period', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_efficiency_reports');
    }
};
