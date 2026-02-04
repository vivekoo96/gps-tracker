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
        Schema::table('devices', function (Blueprint $table) {
            // Add missing GHMC Vehicle & Driver Details
            if (!Schema::hasColumn('devices', 'vehicle_no')) {
                $table->string('vehicle_no')->nullable();
            }
            if (!Schema::hasColumn('devices', 'vehicle_type')) {
                $table->string('vehicle_type')->nullable();
            }
            if (!Schema::hasColumn('devices', 'driver_name')) {
                $table->string('driver_name')->nullable();
            }
            if (!Schema::hasColumn('devices', 'driver_contact')) {
                $table->string('driver_contact')->nullable();
            }
            if (!Schema::hasColumn('devices', 'sim_number')) {
                $table->string('sim_number')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'vehicle_no', 
                'vehicle_type', 
                'driver_name', 
                'driver_contact', 
                'sim_number'
            ]);
        });
    }
};
