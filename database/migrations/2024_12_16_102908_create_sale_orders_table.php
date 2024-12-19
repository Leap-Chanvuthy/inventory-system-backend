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
        Schema::create('sale_orders', function (Blueprint $table) {
            $table->id();
            $table -> string('payment_method' , 50);
            $table -> date('order_date');
            $table -> string('order_status');
            $table -> string('payment_status');
            $table-> double('discount_percentage')->default(0);
            $table-> double('discount_value_in_riel')->default(0);
            $table-> double('discount_value_in_usd')->default(0);
            $table->double('tax_percentage')->default(0);
            $table->double('tax_value_in_riel') -> default(0);
            $table->double('tax_value_in_usd') -> default(0);
            $table->double('sub_total_in_riel');
            $table->double('sub_total_in_usd');
            $table->double('grand_total_with_tax_in_riel');
            $table->double('grand_total_with_tax_in_usd');
            $table->double('grand_total_without_tax_in_riel');
            $table->double('grand_total_without_tax_in_usd');
            $table-> double('clearing_payable_percentage');
            $table -> double('indebted_in_riel') -> default(0);
            $table-> double('indebted_in_usd');
            $table->unsignedBigInteger('customer_id');

            $table -> foreign('customer_id')
            ->references('id')
            ->on('customers')
            ->onDelete('cascade')
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('sale_orders');
    }
};
