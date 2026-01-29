<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gps_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('speed', 8, 2)->default(0);
            $table->decimal('direction', 5, 2)->default(0);
            $table->decimal('altitude', 8, 2)->default(0);
            $table->integer('satellites')->default(0);
            $table->integer('battery_level')->nullable();
            $table->integer('signal_strength')->nullable();
            $table->timestamp('recorded_at');
            $table->text('raw_data')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['device_id', 'recorded_at']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('gps_data');
    }
};
