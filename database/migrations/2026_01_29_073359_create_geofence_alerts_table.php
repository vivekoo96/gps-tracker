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
        Schema::create('geofence_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('geofence_id')->constrained()->onDelete('cascade');
            $table->boolean('alert_on_entry')->default(true);
            $table->boolean('alert_on_exit')->default(true);
            $table->json('notify_users')->nullable(); // array of user IDs to notify
            $table->boolean('email_enabled')->default(false);
            $table->boolean('sms_enabled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geofence_alerts');
    }
};
