<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_part_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->constrained('maintenance_parts')->cascadeOnDelete();
            
            $table->integer('quantity_used');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_cost', 10, 2);
            
            $table->timestamps();
            
            $table->index('maintenance_record_id');
            $table->index('part_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_part_usage');
    }
};
