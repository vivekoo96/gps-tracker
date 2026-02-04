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
        if (!Schema::hasTable('routes')) {
            Schema::create('routes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->json('stops')->nullable(); // JSON array of lat/lng or stops
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('landmarks')) {
            Schema::create('landmarks', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type')->default('General'); // e.g., 'Garage', 'Dump Yard'
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('device_id')->constrained()->cascadeOnDelete();
                $table->string('alert_type'); // SOS, SPEED, BATTERY, etc.
                $table->string('status')->default('OPEN'); // OPEN, IN_PROGRESS, CLOSED
                $table->text('description')->nullable();
                $table->timestamp('raised_at')->useCurrent();
                $table->timestamp('closed_at')->nullable();
                $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ticket_logs')) {
            Schema::create('ticket_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action'); // STATUS_CHANGE, COMMENT, etc.
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_logs');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('landmarks');
        Schema::dropIfExists('routes');
    }
};
