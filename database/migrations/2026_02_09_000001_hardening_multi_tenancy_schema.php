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
        $tables = [
            'zones',
            'circles',
            'wards',
            'transfer_stations',
            'tickets',
            'ticket_logs',
            'fuel_sensors',
            'dashcams',
            'collection_points',
            'emergency_contacts',
            'sos_alerts',
            'device_commands',
            'protocol_logs',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'vendor_id')) {
                        $table->foreignId('vendor_id')->nullable()->after('id')->constrained('vendors')->nullOnDelete();
                        $table->index('vendor_id');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'zones',
            'circles',
            'wards',
            'transfer_stations',
            'tickets',
            'ticket_logs',
            'fuel_sensors',
            'dashcams',
            'collection_points',
            'emergency_contacts',
            'sos_alerts',
            'device_commands',
            'protocol_logs',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'vendor_id')) {
                        $table->dropForeign([$tableName . '_vendor_id_foreign']);
                        $table->dropColumn('vendor_id');
                    }
                });
            }
        }
    }
};
