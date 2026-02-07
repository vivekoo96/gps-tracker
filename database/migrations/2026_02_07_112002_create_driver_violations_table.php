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
        Schema::create('driver_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->enum('violation_type', [
                'harsh_braking',
                'harsh_acceleration',
                'harsh_cornering',
                'speeding',
                'excessive_idling',
                'seatbelt',
                'phone_usage'
            ]);
            
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('address')->nullable();
            
            $table->decimal('speed', 5, 2)->nullable(); // km/h
            $table->decimal('speed_limit', 5, 2)->nullable(); // km/h
            
            $table->json('metadata')->nullable(); // g-force, duration, etc.
            
            $table->timestamp('occurred_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['vendor_id', 'driver_id']);
            $table->index(['vendor_id', 'violation_type']);
            $table->index(['vendor_id', 'severity']);
            $table->index('occurred_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_violations');
    }
};
