<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            // GPS tracking fields
            $table->decimal('latitude', 10, 8)->nullable()->after('status');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->integer('speed')->default(0)->after('longitude'); // km/h
            $table->integer('battery_level')->default(100)->after('speed'); // percentage
            $table->timestamp('last_location_update')->nullable()->after('battery_level');
            $table->string('location_address')->nullable()->after('last_location_update');
            
            // Additional tracking fields
            $table->boolean('is_moving')->default(false)->after('location_address');
            $table->decimal('heading', 5, 2)->nullable()->after('is_moving'); // degrees
            $table->integer('altitude')->nullable()->after('heading'); // meters
            $table->integer('satellites')->nullable()->after('altitude'); // GPS satellite count
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude', 
                'speed',
                'battery_level',
                'last_location_update',
                'location_address',
                'is_moving',
                'heading',
                'altitude',
                'satellites'
            ]);
        });
    }
};
