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
        Schema::create('driver_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            
            $table->enum('alert_type', [
                'critical_violation',
                'pattern_detected',
                'score_drop',
                'safety_concern',
                'achievement',
                'reminder'
            ]);
            
            $table->string('title');
            $table->text('message');
            
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            
            $table->json('metadata')->nullable(); // Additional context
            
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at');
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['vendor_id', 'driver_id', 'is_read']);
            $table->index(['vendor_id', 'alert_type']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_alerts');
    }
};
