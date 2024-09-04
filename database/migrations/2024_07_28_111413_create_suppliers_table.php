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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table -> string('image' , 255) -> nullable();
            $table -> string('name' , 255);
            $table -> string('phone_number', 50);
            $table -> string('location' , 255);
            $table -> string('longitude' , 100) -> nullable();
            $table -> string('latitude' , 100) -> nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('email', 255)->unique();
            $table->string('contact_person', 255);
            $table->string('business_registration_number', 100)->nullable();
            $table->string('vat_number', 100)->nullable(); 
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_name', 50)->nullable();
            $table->string('bank_name', 255)->nullable();
            $table -> text('note') -> nullable();
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
        Schema::dropIfExists('suppliers');
    }
};
