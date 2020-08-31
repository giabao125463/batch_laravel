<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Commodity
 * @package App\Models
 * @version October 16, 2019, 6:39 pm JST
 */
class Commodity extends Model
{
    public $fillable = [
        'ordernum',
        'name',
        'brandcode',
        'orgcode',
        'orgoptioncode',
        'jancode',
        'dcrate',
        'price',
        'amount',
        'consumption_tax_rate',
        'point',
        'order_id',
    ];

    protected $casts = [
        'point' => 'array',
    ];
}
