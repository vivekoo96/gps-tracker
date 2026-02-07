<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            
            $table->enum('alert_type', ['low_fuel', 'theft_detected', 'abnormal_consumption', 'refuel_needed'])->default('low_fuel');
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            
            $table->string('title');
            $table->text('message');
            $table->decimal('fuel_level', 8, 2)->nullable();
            $table->json('metadata')->nullable();
            
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at');
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['vendor_id', 'device_id', 'is_read']);
            $table->index(['vendor_id', 'alert_type', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_alerts');
    }
};
