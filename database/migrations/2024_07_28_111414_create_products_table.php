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
            $table->date('staging_date') -> nullable();
            $table -> string('product_name' , 255);
            $table -> string('product_code' , 255);
            $table -> integer('quantity');
            $table -> integer('remaining_quantity');
            $table -> integer('minimum_stock_level');
            $table -> string('unit_of_measurement' ,255);
            $table -> string('package_size' ,255) -> nullable();
            $table -> string('warehouse_location' ,255)->nullable();
            $table -> double('unit_price_in_usd');
            $table -> double('total_value_in_usd');
            $table -> double('exchange_rate_from_usd_to_riel');
            $table -> double('unit_price_in_riel');
            $table -> double('total_value_in_riel');
            $table -> double('exchange_rate_from_riel_to_usd');
            $table -> text('description')->nullable();
            $table -> string('status' , 255);
            $table -> unsignedBigInteger('product_category_id') -> nullable();



            $table->timestamps();
            $table->softDeletes();

            $table -> foreign('product_category_id')
            -> references('id')
            -> on('product_categories')
            -> onDelete('set null')
            -> onUpdate('cascade');
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
