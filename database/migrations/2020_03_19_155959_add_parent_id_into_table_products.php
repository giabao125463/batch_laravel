<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdIntoTableProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('parent_code', 50)->index()->nullable();
            $table->string('option_id', 10)->nullable();
            $table->json('member_group_prices')->nullable();
            $table->string('main_content', 2000)->nullable()->change();
            $table->string('main_content2', 2000)->nullable()->change();
            $table->string('smartphone_content1', 2000)->nullable()->change();
            $table->string('smartphone_content2', 2000)->nullable()->change();
            $table->string('product_list_content', 2000)->nullable()->change();
            $table->string('is_display_product_list_content', 10)->nullable()->change();
            $table->string('is_display_mobile_content', 10)->nullable()->change();
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
            $table->dropColumn('parent_code');
            $table->dropColumn('option_id');
            $table->dropColumn('member_group_prices');
        });
    }
}
