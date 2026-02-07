<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('protocol_type')->default('auto')->after('device_type');
            // Options: 'auto', 'gt06', 'tk103', 'concox', 'teltonika', 'text'
            
            $table->json('protocol_config')->nullable()->after('protocol_type');
            // Store protocol-specific configuration
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['protocol_type', 'protocol_config']);
        });
    }
};
