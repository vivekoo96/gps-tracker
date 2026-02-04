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
            // GHMC Administrative Hierarchy
            if (!Schema::hasColumn('devices', 'zone_id')) {
                $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('devices', 'circle_id')) {
                $table->foreignId('circle_id')->nullable()->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('devices', 'ward_id')) {
                $table->foreignId('ward_id')->nullable()->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('devices', 'transfer_station_id')) {
                $table->foreignId('transfer_station_id')->nullable()->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropForeign(['circle_id']);
            $table->dropForeign(['ward_id']);
            $table->dropForeign(['transfer_station_id']);
            
            $table->dropColumn([
                'vehicle_no', 'vehicle_type', 'driver_name', 
                'driver_contact', 'sim_number', 
                'zone_id', 'circle_id', 'ward_id', 'transfer_station_id'
            ]);
        });
    }
};
