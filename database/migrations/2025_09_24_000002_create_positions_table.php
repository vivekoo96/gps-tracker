<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->timestamp('fix_time');
            $table->decimal('latitude', 10, 6);
            $table->decimal('longitude', 10, 6);
            $table->decimal('speed', 8, 2)->nullable(); // km/h
            $table->decimal('course', 8, 2)->nullable(); // degrees
            $table->decimal('altitude', 8, 2)->nullable(); // meters
            $table->unsignedTinyInteger('satellites')->nullable();
            $table->boolean('ignition')->nullable();
            $table->json('attributes')->nullable();
            $table->text('raw')->nullable();
            $table->timestamps();
            $table->index(['device_id', 'fix_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};


