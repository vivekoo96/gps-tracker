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
        if (!Schema::hasTable('zones')) {
            Schema::create('zones', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('circles')) {
            Schema::create('circles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->unique(['zone_id', 'name']);
            });
        }

        if (!Schema::hasTable('wards')) {
            Schema::create('wards', function (Blueprint $table) {
                $table->id();
                $table->foreignId('circle_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->unique(['circle_id', 'name']);
            });
        }

        if (!Schema::hasTable('transfer_stations')) {
            Schema::create('transfer_stations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ward_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->decimal('capacity', 10, 2)->default(0); // In metric tons
                $table->decimal('current_load', 10, 2)->default(0); // In metric tons, updated live
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_stations');
        Schema::dropIfExists('wards');
        Schema::dropIfExists('circles');
        Schema::dropIfExists('zones');
    }
};
