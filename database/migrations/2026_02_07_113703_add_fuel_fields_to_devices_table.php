<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->decimal('fuel_capacity', 8, 2)->nullable()->after('status'); // Tank capacity in liters
            $table->enum('fuel_type', ['petrol', 'diesel', 'cng', 'electric'])->nullable()->after('fuel_capacity');
            $table->decimal('current_fuel_level', 8, 2)->nullable()->after('fuel_type'); // Current fuel in liters or %
            $table->timestamp('last_fuel_update')->nullable()->after('current_fuel_level');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['fuel_capacity', 'fuel_type', 'current_fuel_level', 'last_fuel_update']);
        });
    }
};
