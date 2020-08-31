<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommoditiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commodities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ordernum', 20)->index();
            $table->string('name', 100)->nullable();
            $table->string('brandcode', 50)->nullable();
            $table->string('orgcode', 50)->nullable();
            $table->string('jancode', 50)->nullable();
            $table->integer('dcrate')->nullable();
            $table->integer('price')->nullable();
            $table->integer('amount')->nullable();
            $table->integer('consumption_tax_rate')->nullable();
            $table->string('nameoption', 50)->nullable();
            $table->json('point')->nullable();
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
        Schema::dropIfExists('commodities');
    }
}
