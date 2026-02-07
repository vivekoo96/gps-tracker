<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            
            $table->string('part_number');
            $table->string('part_name');
            $table->string('category');
            $table->string('manufacturer')->nullable();
            
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('minimum_stock_level')->default(5);
            
            $table->string('location')->nullable();
            $table->string('supplier')->nullable();
            $table->string('supplier_contact')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->unique(['vendor_id', 'part_number']);
            $table->index(['vendor_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_parts');
    }
};
