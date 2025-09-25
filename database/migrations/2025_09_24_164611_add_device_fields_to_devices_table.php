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
            // General fields
            $table->string('unit_type')->nullable();
            $table->string('device_type')->nullable();
            $table->string('server_address')->nullable();
            $table->string('unique_id')->nullable()->unique();
            $table->string('phone_number')->nullable();
            $table->string('password')->nullable();
            $table->string('creator')->nullable();
            $table->string('account')->nullable();
            
            // Sensor fields
            $table->string('mileage_counter')->default('GPS');
            $table->decimal('mileage_current_value', 10, 2)->default(0);
            $table->boolean('mileage_auto')->default(false);
            $table->string('engine_hours_counter')->default('Engine ignition sensor');
            $table->decimal('engine_hours_current_value', 10, 2)->default(0);
            $table->boolean('engine_hours_auto')->default(false);
            $table->integer('gprs_traffic_counter')->default(0);
            $table->boolean('gprs_traffic_auto')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'unit_type', 'device_type', 'server_address', 'unique_id', 
                'phone_number', 'password', 'creator', 'account',
                'mileage_counter', 'mileage_current_value', 'mileage_auto',
                'engine_hours_counter', 'engine_hours_current_value', 'engine_hours_auto',
                'gprs_traffic_counter', 'gprs_traffic_auto'
            ]);
        });
    }
};
