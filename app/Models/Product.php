<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 * @package App\Models
 * @version October 16, 2019, 6:39 pm JST
 */
class Product extends Model
{
    protected $primaryKey = 'ubrand_code';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'option'              => 'array',
        'option_group'        => 'array',
        'name_option_group'   => 'array',
        'basic_category'      => 'array',
        'categories'          => 'array',
        'google_shopping'     => 'array',
        'member_group_prices' => 'array',
    ];

    public $fillable = [
        'ubrand_code',
        'created_date',
        'modified_date',
        'product_id',
        'brand_code',
        'is_display',
        'is_member_only',
        'is_reduced_tax_rate_brand',
        'product_name',
        'weight',
        'price',
        'consumption_tax_rate',
        'point',
        'fixed_price',
        'purchase_price',
        'jancode',
        'vendor',
        'origin',
        'is_display_origin',
        'stock',
        'is_diplay_stock',
        'minimum_quantity',
        'maximum_quantity',
        'collections',
        'individual_shipping',
        'is_publish_date_from',
        'publish_date_from',
        'is_publish_date_to',
        'publish_date_to',
        'is_discount_rate',
        'discount_rate',
        'discount_term',
        'item_group',
        'search_keyword',
        'note',
        'product_page_url',
        'zoom_image_url',
        'image_url',
        'thumbnail_image_url',
        'sub_image1_url',
        'sub_image1_content',
        'sub_image2_url',
        'sub_image2_content',
        'sub_image3_url',
        'sub_image3_content',
        'sub_image_layout',
        'main_content',
        'main_content2',
        'smartphone_content1',
        'smartphone_content2',
        'product_list_content',
        'order_page_note',
        'is_display_product_list_content',
        'is_display_mobile_content',
        'is_restock_notification',
        'option',
        'option_group',
        'name_option_group',
        'basic_category',
        'categories',
        'google_shopping',
        'parent_code',
        'option_id',
        'member_group_prices',
    ];

    /**
     * Get all product options
     *
     * @return array
     */
    public function productOptions()
    {
        return $this->hasMany(Product::class, 'parent_code', 'ubrand_code');
    }

    /**
     * Get parent product
     *
     * @return void
     */
    public function parent()
    {

        return $this->belongsTo(Product::class, 'parent_code', 'ubrand_code');
    }

    /**
     * CHeck product is parent
     *
     * @return bool
     */
    public function getIsParentAttribute()
    {

        return empty($this->parent_code);
    }
}
