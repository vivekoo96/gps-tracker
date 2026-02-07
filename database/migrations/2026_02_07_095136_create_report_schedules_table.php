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
        Schema::create('report_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('report_templates')->cascadeOnDelete();
            $table->string('frequency', 20); // daily, weekly, monthly
            $table->time('time'); // time to generate
            $table->json('days_of_week')->nullable(); // for weekly: [1,2,3,4,5]
            $table->integer('day_of_month')->nullable(); // for monthly: 1-31
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('template_id');
            $table->index(['is_active', 'next_run_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_schedules');
    }
};
