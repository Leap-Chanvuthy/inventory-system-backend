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

            $table -> string('material_code' , 255);
            $table -> integer('quantity');
            $table -> decimal('unit_price' , 10,2);
            $table -> decimal('total_value' , 10, 2);
            $table -> integer('minimum_stock_level');
            $table -> string('raw_material_category' ,100);
            $table -> string('unit_of_measurement', 100);
            $table -> string('package_size' , 100) -> nullable();
            $table -> string('status' , 100) -> nullable();
            $table -> string('location' , 100) -> nullable();
            $table -> text('description') -> nullable();
            $table -> date('expiry_date') -> nullable();
            $table-> unsignedBigInteger('supplier_id') -> nullable();
            $table->unsignedBigInteger('currency_id')->nullable();

            $table->foreign('currency_id')
            ->references('id')
            ->on('currencies')
            ->onDelete('cascade')
            ->onUpdate('cascade');
            
            $table -> foreign('supplier_id')
            ->references('id')
            ->on('suppliers')
            ->onDelete('cascade')
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('raw_materials');
    }
};
