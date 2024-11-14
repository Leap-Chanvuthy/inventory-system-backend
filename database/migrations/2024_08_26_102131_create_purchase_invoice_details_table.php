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
        Schema::create('purchase_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity');
            $table->double('total_price_in_riel');
            $table->double('total_price_in_usd');
            $table->unsignedBigInteger('purchase_invoice_id');
            $table->unsignedBigInteger('raw_material_id');
            $table-> unsignedBigInteger('supplier_id') -> nullable();

            $table->foreign('purchase_invoice_id')
                ->references('id')
                ->on('purchase_invoices')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('raw_material_id')
                ->references('id')
                ->on('raw_materials')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table -> foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
                -> onDelete('cascade')
                ->onUpdate('cascade');

            $table->timestamps();
            $table -> softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_details');
    }
};
