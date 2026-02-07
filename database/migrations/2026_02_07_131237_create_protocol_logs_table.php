<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('protocol_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->string('protocol_type');
            $table->text('raw_data');
            $table->json('parsed_data')->nullable();
            $table->boolean('parse_success')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['device_id', 'created_at']);
            $table->index('protocol_type');
            $table->index('parse_success');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protocol_logs');
    }
};
