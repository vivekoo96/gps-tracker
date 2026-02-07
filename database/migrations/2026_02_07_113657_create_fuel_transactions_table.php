<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('vehicle_id')->nullable(); // No FK constraint - vehicles table may not exist
            
            $table->enum('transaction_type', ['refuel', 'consumption', 'theft', 'adjustment'])->default('consumption');
            
            $table->decimal('fuel_before', 8, 2)->nullable(); // Liters or %
            $table->decimal('fuel_after', 8, 2)->nullable();
            $table->decimal('fuel_change', 8, 2); // Calculated difference
            
            $table->decimal('odometer', 10, 2)->nullable(); // km
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            $table->decimal('cost', 10, 2)->nullable();
            $table->decimal('price_per_liter', 8, 2)->nullable();
            $table->string('station_name')->nullable();
            $table->string('receipt_image')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamp('detected_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index(['vendor_id', 'device_id', 'detected_at']);
            $table->index(['vendor_id', 'transaction_type']);
            $table->index(['device_id', 'transaction_type', 'detected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_transactions');
    }
};
