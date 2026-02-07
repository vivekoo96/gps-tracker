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
        // Daily Performance Metrics
        Schema::create('daily_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('vehicle_id')->constrained('devices')->onDelete('cascade');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
            $table->foreignId('ward_id')->nullable()->constrained('wards')->onDelete('set null');
            $table->decimal('total_distance', 10, 2)->default(0);
            $table->integer('total_duration')->default(0); // seconds
            $table->integer('idle_time')->default(0); // seconds
            $table->integer('moving_time')->default(0); // seconds
            $table->integer('collections_completed')->default(0);
            $table->integer('collections_missed')->default(0);
            $table->decimal('efficiency_score', 5, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['date', 'vehicle_id']);
        });

        // Monthly Rankings
        Schema::create('monthly_rankings', function (Blueprint $table) {
            $table->id();
            $table->date('month');
            $table->enum('entity_type', ['ward', 'circle', 'zone', 'sat', 'vehicle']);
            $table->unsignedBigInteger('entity_id');
            $table->integer('rank');
            $table->decimal('score', 10, 2)->default(0);
            $table->integer('collections_completed')->default(0);
            $table->decimal('efficiency_percentage', 5, 2)->default(0);
            $table->timestamps();
            
            $table->index(['month', 'entity_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_rankings');
        Schema::dropIfExists('daily_performance_metrics');
    }
};
