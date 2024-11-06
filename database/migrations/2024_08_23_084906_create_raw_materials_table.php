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

            $table -> string('material_code' , 255) -> unique();
            $table -> integer('quantity');
            $table -> integer('remaining_quantity');
            $table -> double('unit_price_in_usd');
            $table -> double('total_value_in_usd');
            $table -> double('unit_price_in_riel');
            $table -> double('total_value_in_riel');
            $table -> integer('minimum_stock_level');
            $table -> string('unit_of_measurement', 100);
            $table -> string('package_size' , 100) -> nullable();
            $table -> string('status' , 100) -> nullable();
            $table -> string('location' , 100) -> nullable();
            $table -> text('description') -> nullable();
            $table -> date('expiry_date') -> nullable();
            $table-> unsignedBigInteger('supplier_id') -> nullable();
            $table -> unsignedBigInteger('raw_material_category_id') -> nullable();

            $table -> foreign('supplier_id')
            ->references('id')
            ->on('suppliers')
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table -> foreign('raw_material_category_id')
            -> references('id')
            -> on('raw_material_categories')
            -> onDelete('set null')
            -> onUpdate('cascade');


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('raw_materials');
    }
};
