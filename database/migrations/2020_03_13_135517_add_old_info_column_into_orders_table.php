<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOldInfoColumnIntoOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('old_postsuban')->nullable();
            $table->integer('old_sumprice')->nullable();
            $table->datetime('old_order_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('old_postsuban');
            $table->dropColumn('old_sumprice');
            $table->dropColumn('old_order_date');
        });
    }
}
