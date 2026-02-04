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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->after('id')->constrained('vendors')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('vendor_id'); // super_admin, vendor_admin, user
            }
        });

        Schema::table('devices', function (Blueprint $table) {
            if (!Schema::hasColumn('devices', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->after('id')->constrained('vendors')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('devices', 'device_type')) {
                $table->enum('device_type', ['gps', 'fuel', 'dashcam'])->default('gps')->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['vendor_id', 'role']);
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['vendor_id', 'device_type']);
        });
    }
};
