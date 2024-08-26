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
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->double('total_amount');
            $table->string('payment_method' , 50);
            $table-> string('invoice_number' , 100);
            $table->date('payment_date');
            $table-> double('discount_percentage')->default(0);
            $table-> double('discount_value')->default(0);
            $table->double('tax_percentage')->default(0);
            $table->double('tax_value') -> default(0);
            $table -> string('status');
            $table->double('sub_total');
            $table->double('grand_total');
            $table-> double('clearing_payable');
            $table -> double('indebted') -> default(0);
            $table-> unsignedBigInteger('supplier_id');

            $table -> foreign('supplier_id')
            ->references('id')
            ->on('suppliers')
            -> onDelete('cascade')
            ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
    }
};
