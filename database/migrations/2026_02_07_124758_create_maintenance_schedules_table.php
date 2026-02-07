<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('vehicle_type')->nullable(); // For templates
            
            $table->string('task_name');
            $table->text('description')->nullable();
            $table->enum('category', ['engine', 'transmission', 'brakes', 'tires', 'electrical', 'body', 'other'])->default('other');
            
            $table->enum('interval_type', ['odometer', 'time', 'both'])->default('odometer');
            $table->integer('interval_km')->nullable(); // Kilometers between services
            $table->integer('interval_days')->nullable(); // Days between services
            
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->integer('estimated_duration')->nullable(); // Minutes
            
            $table->integer('reminder_km_before')->default(500); // Remind X km before due
            $table->integer('reminder_days_before')->default(7); // Remind X days before due
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['vendor_id', 'device_id', 'is_active']);
            $table->index(['vendor_id', 'vehicle_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
