<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Delivery
 * @package App\Models
 * @version October 16, 2019, 6:39 pm JST
 */
class Delivery extends Model
{
    public $fillable = [
        'ordernum',
        'delivery_id',
        'name',
        'kana',
        'tel',
        'zip',
        'area',
        'address',
        'city',
        'street',
        'deliverydate',
        'deliverytime',
        'delivery_status',
        'scheduled_shipping_date',
        'shipping_date',
        'delivery_order',
        'carrier',
        'daliverynum',
        'carriage',
        'commodities',
        'order_id',
    ];

    protected $casts = [
        'carriage'    => 'array',
        'commodities' => 'array',
    ];
}
