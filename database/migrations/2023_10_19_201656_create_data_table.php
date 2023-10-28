<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 15)->index();
            $table->string('firstname', 50);
            $table->string('lastname', 50);
            $table->string('address', 255);
            $table->string('city', 50);
            $table->string('state', 2);
            $table->string('zip_code', 10);
            $table->integer('age');
            $table->string('income_range', 50);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data');
    }
};
