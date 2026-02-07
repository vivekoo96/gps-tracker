<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('maintenance_schedules')->nullOnDelete();
            
            $table->string('task_name');
            $table->text('description')->nullable();
            $table->enum('category', ['engine', 'transmission', 'brakes', 'tires', 'electrical', 'body', 'other'])->default('other');
            $table->enum('service_type', ['scheduled', 'unscheduled', 'repair', 'inspection'])->default('scheduled');
            
            $table->decimal('odometer_reading', 10, 2);
            $table->date('service_date');
            $table->decimal('next_service_km', 10, 2)->nullable();
            $table->date('next_service_date')->nullable();
            
            $table->decimal('cost', 10, 2)->default(0);
            $table->decimal('labor_cost', 10, 2)->nullable();
            $table->decimal('parts_cost', 10, 2)->nullable();
            
            $table->string('service_provider')->nullable();
            $table->string('technician_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('invoice_image')->nullable();
            $table->text('notes')->nullable();
            
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['vendor_id', 'device_id', 'service_date']);
            $table->index(['vendor_id', 'category']);
            $table->index(['device_id', 'odometer_reading']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
