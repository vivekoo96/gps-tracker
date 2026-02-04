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
        Schema::create('dashcams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $table->string('camera_model')->nullable();
            $table->string('resolution')->default('1080p');
            $table->string('storage_capacity')->nullable()->comment('e.g. 128GB');
            $table->string('stream_url')->nullable();
            $table->string('status')->default('offline'); // online, offline, recording
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashcams');
    }
};
