<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnTypeInTableProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('sub_image1_content')->nullable()->change();
            $table->text('sub_image2_content')->nullable()->change();
            $table->text('sub_image3_content')->nullable()->change();
            $table->text('main_content')->nullable()->change();
            $table->text('main_content2')->nullable()->change();
            $table->text('smartphone_content1')->nullable()->change();
            $table->text('smartphone_content2')->nullable()->change();
            $table->text('order_page_note')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
}
