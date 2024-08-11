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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table -> string('item_name' , 255);
            $table -> string('description' , 255);
            $table -> integer('quantity');
            $table -> double('cost_per_item');
            $table -> double('total_value');
            $table -> string('category' , 255);
            $table -> string('unit' , 255);
            $table-> unsignedBigInteger('supplier_id');
            $table -> foreign('supplier_id')
            ->references('id')
            ->on('suppliers')
            -> onDelete('cascade')
            ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('products');
    }
};
