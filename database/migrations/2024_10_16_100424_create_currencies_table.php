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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table -> string('base_currency_name' , 50);
            $table -> string('symbol' ,50);
            $table -> double('base_currency_value');
            $table -> string('target_currency_name' , 50);
            $table -> double('target_currency_value');
            $table -> double('exchage_rate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('currencies');
    }
};
