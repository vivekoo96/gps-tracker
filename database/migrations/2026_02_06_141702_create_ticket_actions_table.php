<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('action_type', ['created', 'assigned', 'comment', 'status_change', 'resolved', 'closed']);
            $table->text('action_description')->nullable();
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index('ticket_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_actions');
    }
};
