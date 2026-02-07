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
        Schema::create('driver_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            
            $table->enum('period', ['daily', 'weekly', 'monthly', 'all_time'])->default('daily');
            $table->date('period_start');
            $table->date('period_end');
            
            // Overall scores (0-100)
            $table->decimal('score', 5, 2)->default(100);
            $table->decimal('safety_score', 5, 2)->default(100);
            $table->decimal('efficiency_score', 5, 2)->default(100);
            $table->decimal('compliance_score', 5, 2)->default(100);
            
            // Trip statistics
            $table->integer('total_trips')->default(0);
            $table->decimal('total_distance', 10, 2)->default(0); // km
            $table->integer('total_violations')->default(0);
            
            // Violation counts
            $table->integer('harsh_braking_count')->default(0);
            $table->integer('harsh_acceleration_count')->default(0);
            $table->integer('harsh_cornering_count')->default(0);
            $table->integer('speeding_count')->default(0);
            $table->integer('idling_count')->default(0);
            
            // Rankings
            $table->integer('rank')->nullable();
            $table->enum('performance_level', ['excellent', 'good', 'fair', 'poor'])->nullable();
            
            $table->timestamp('calculated_at');
            $table->timestamps();
            
            $table->unique(['vendor_id', 'driver_id', 'period', 'period_start']);
            $table->index(['vendor_id', 'period', 'score']);
            $table->index(['vendor_id', 'period', 'rank']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_scores');
    }
};
