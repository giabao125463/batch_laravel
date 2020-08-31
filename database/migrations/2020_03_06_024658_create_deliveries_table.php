<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ordernum', 20)->index();
            $table->string('delivery_id', 50)->nullable();
            $table->string('name', 50)->nullable();
            $table->string('kana', 50)->nullable();
            $table->string('tel', 50)->nullable();
            $table->string('zip', 50)->nullable();
            $table->string('area', 50)->nullable();
            $table->string('address', 50)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('street', 50)->nullable();
            $table->string('deliverydate', 14)->nullable();
            $table->string('deliverytime', 14)->nullable();
            $table->integer('delivery_status')->nullable();
            $table->datetime('scheduled_shipping_date')->nullable();
            $table->datetime('shipping_date')->nullable();
            $table->integer('delivery_order')->nullable();
            $table->string('carrier', 10)->nullable();
            $table->string('daliverynum', 50)->nullable();
            $table->json('carriage')->nullable();
            $table->json('commodities')->nullable();
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
        Schema::dropIfExists('deliveries');
    }
}
