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
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table -> string('name' , 50);
            $table -> integer('quantity');
            $table -> string('image')->nullable();
            $table -> decimal('unit_price' , 10,2);
            $table -> decimal('total_value' , 10, 2);
            $table -> integer('minimum_stock_level');
            $table -> string('unit', 100);
            $table -> string('package_size' , 100);
            $table-> unsignedBigInteger('supplier_id');
            $table-> unsignedBigInteger('product_id')->nullable();
            
            $table -> foreign('supplier_id')
            ->references('id')
            ->on('suppliers')
            -> onDelete('cascade')
            ->onUpdate('cascade');

            $table -> foreign('product_id')
            ->references('id')
            ->on('products')
            -> onDelete('set null')
            ->onUpdate('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_materials');
    }
};
