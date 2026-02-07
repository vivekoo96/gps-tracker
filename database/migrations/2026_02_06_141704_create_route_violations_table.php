<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_violations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->date('violation_date');
            $table->integer('violation_count')->default(1);
            $table->timestamp('last_violation_at')->nullable();
            $table->boolean('warning_sent')->default(false);
            $table->timestamps();
            
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
            
            $table->unique(['device_id', 'violation_date']);
            $table->index('violation_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_violations');
    }
};
