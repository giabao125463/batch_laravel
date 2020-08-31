<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeOrderPrimaryKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('commodities')->truncate();
        DB::table('deliveries')->truncate();
        Schema::dropIfExists('orders');
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ordernum', 20)->index();
            $table->integer('postsuban')->nullable();
            $table->integer('status')->default(0)->nullable();
            $table->datetime('date')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('ordermemo')->nullable()->nullable();
            $table->json('notes')->nullable();
            $table->integer('carriage')->nullable();
            $table->json('usepoint')->nullable();
            $table->integer('sumprice')->nullable();
            $table->json('price_per_tax_rate_list')->nullable();
            $table->string('buyer_id', 50)->nullable();
            $table->string('buyer_name', 50)->nullable();
            $table->string('buyer_kana', 50)->nullable();
            $table->string('buyer_tel', 50)->nullable();
            $table->string('buyer_tel2', 50)->nullable();
            $table->string('buyer_email', 50)->nullable();
            $table->string('buyer_zip', 50)->nullable();
            $table->string('buyer_address', 200)->nullable();
            $table->string('buyer_area', 50)->nullable();
            $table->string('buyer_city', 200)->nullable();
            $table->string('buyer_street', 200)->nullable();
            $table->json('buyer_membergroup')->nullable();
            $table->integer('bulk')->nullable();
            $table->string('coupon', 50)->nullable();
            $table->string('couponcode', 50)->nullable();
            $table->string('couponname', 50)->nullable();
            $table->integer('old_postsuban')->nullable();
            $table->integer('old_sumprice')->nullable();
            $table->datetime('old_order_date')->nullable();
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
    }
}
