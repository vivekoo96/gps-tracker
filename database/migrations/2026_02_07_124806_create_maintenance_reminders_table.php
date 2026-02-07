<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained('maintenance_schedules')->cascadeOnDelete();
            
            $table->enum('reminder_type', ['upcoming', 'overdue', 'critical'])->default('upcoming');
            $table->string('task_name');
            
            $table->decimal('due_km', 10, 2)->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('current_km', 10, 2);
            $table->decimal('km_remaining', 10, 2)->nullable();
            $table->integer('days_remaining')->nullable();
            
            $table->text('message');
            $table->boolean('is_sent')->default(false);
            $table->boolean('is_acknowledged')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['vendor_id', 'device_id', 'is_acknowledged']);
            $table->index(['vendor_id', 'reminder_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_reminders');
    }
};
