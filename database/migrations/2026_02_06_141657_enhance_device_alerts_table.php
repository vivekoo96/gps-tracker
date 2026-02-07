<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_alerts', function (Blueprint $table) {
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->after('alert_type');
            $table->enum('alert_category', ['device', 'route', 'service', 'emergency'])->after('severity');
            $table->text('description')->nullable()->after('alert_category');
            $table->json('metadata')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('device_alerts', function (Blueprint $table) {
            $table->dropColumn(['severity', 'alert_category', 'description', 'metadata']);
        });
    }
};
