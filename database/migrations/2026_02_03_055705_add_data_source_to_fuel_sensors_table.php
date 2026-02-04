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
        Schema::table('fuel_sensors', function (Blueprint $table) {
            $table->string('data_source')->default('adc1')->after('calibration_data')->comment('Key in GPS data (e.g., adc1, fuel, io_val)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_sensors', function (Blueprint $table) {
            $table->dropColumn('data_source');
        });
    }
};
