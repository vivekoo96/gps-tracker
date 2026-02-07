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
            $table->string('collection_route')->nullable()->after('ward_id');
            $table->foreignId('current_collection_point_id')->nullable()->after('collection_route')->constrained('collection_points')->onDelete('set null');
            $table->integer('collections_today')->default(0)->after('current_collection_point_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['current_collection_point_id']);
            $table->dropColumn(['collection_route', 'current_collection_point_id', 'collections_today']);
        });
    }
};
