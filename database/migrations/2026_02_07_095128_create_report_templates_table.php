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
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('type', 50); // trip, mileage, speed, idle, geofence, fuel, driver, maintenance
            $table->text('description')->nullable();
            $table->json('columns'); // selected columns to display
            $table->json('filters'); // date range, devices, etc.
            $table->json('grouping')->nullable(); // group by device, driver, date
            $table->json('sorting')->nullable(); // sort columns
            $table->string('schedule')->nullable(); // daily, weekly, monthly, none
            $table->json('recipients')->nullable(); // email addresses
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['vendor_id', 'type']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};
