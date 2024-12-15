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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table -> string('image') -> nullable();
            $table -> string('fullname', 50);
            $table -> string('email_address' , 50) -> nullable();
            $table -> string('phone_number' , 50);
            $table -> string('social_media' , 100)-> nullable();
            $table -> string('shipping_address' , 255);
            $table->string('longitude', 100)->nullable();
            $table->string('latitude', 100)->nullable();
            $table -> string('customer_status' , 255);
            $table -> unsignedBigInteger('customer_category_id') -> nullable();
            $table -> text('customer_note');

            $table -> foreign('customer_category_id')
            -> references('id')
            -> on('customer_categories')
            -> onDelete('set null')
            -> onUpdate('cascade');
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
        Schema::dropIfExists('customers');
    }
};
