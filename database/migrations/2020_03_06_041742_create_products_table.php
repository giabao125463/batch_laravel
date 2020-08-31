<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->string('ubrand_code', 50)->primary();
            $table->string('created_date', 14)->nullable();
            $table->string('modified_date', 14)->nullable();
            $table->string('product_id', 10)->nullable();
            $table->string('brand_code', 12)->nullable();
            $table->string('is_display', 10)->nullable();
            $table->string('is_member_only', 10)->nullable();
            $table->string('is_reduced_tax_rate_brand', 10)->nullable();
            $table->string('product_name')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('price')->nullable();
            $table->integer('consumption_tax_rate')->nullable();
            $table->integer('point')->nullable();
            $table->integer('fixed_price')->nullable();
            $table->integer('purchase_price')->nullable();
            $table->string('jancode', 20)->nullable();
            $table->string('vendor', 50)->nullable();
            $table->string('origin', 50)->nullable();
            $table->integer('is_display_origin')->nullable();
            $table->integer('stock')->nullable();
            $table->integer('is_diplay_stock')->nullable();
            $table->integer('minimum_quantity')->nullable();
            $table->integer('maximum_quantity')->nullable();
            $table->integer('collections')->nullable();
            $table->string('individual_shipping', 50)->nullable();
            $table->integer('is_publish_date_from')->nullable();
            $table->string('publish_date_from', 14)->nullable();
            $table->integer('is_publish_date_to')->nullable();
            $table->string('publish_date_to', 14)->nullable();
            $table->integer('is_discount_rate')->nullable();
            $table->integer('discount_rate')->nullable();
            $table->string('discount_term', 50)->nullable();
            $table->string('item_group', 50)->nullable();
            $table->string('search_keyword', 50)->nullable();
            $table->string('note', 500)->nullable();
            $table->string('product_page_url', 500)->nullable();
            $table->string('zoom_image_url', 500)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->string('thumbnail_image_url', 500)->nullable();
            $table->string('sub_image1_url', 500)->nullable();
            $table->string('sub_image1_content', 50)->nullable();
            $table->string('sub_image2_url', 500)->nullable();
            $table->string('sub_image2_content', 50)->nullable();
            $table->string('sub_image3_url', 500)->nullable();
            $table->string('sub_image3_content', 50)->nullable();
            $table->string('sub_image_layout', 50)->nullable();
            $table->text('main_content')->nullable();
            $table->text('main_content2')->nullable();
            $table->text('smartphone_content1')->nullable();
            $table->text('smartphone_content2')->nullable();
            $table->text('product_list_content', 50)->nullable();
            $table->string('order_page_note', 50)->nullable();
            $table->text('is_display_product_list_content', 10)->nullable();
            $table->text('is_display_mobile_content', 10)->nullable();
            $table->string('is_restock_notification', 10)->nullable();
            $table->json('option')->nullable();
            $table->json('option_group')->nullable();
            $table->json('name_option_group')->nullable();
            $table->json('basic_category')->nullable();
            $table->json('categories')->nullable();
            $table->json('google_shopping')->nullable();
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
        Schema::dropIfExists('products');
    }
}
