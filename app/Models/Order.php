<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Order
 * @package App\Models
 * @version October 16, 2019, 6:39 pm JST
 */
class Order extends Model
{

    protected $casts = [
        'notes'                   => 'array',
        'usepoint'                => 'array',
        'price_per_tax_rate_list' => 'array',
        'buyer_membergroup'       => 'array',
        'old_order_date'          => 'datetime',
        'date'                    => 'datetime',
    ];

    public $fillable = [
        'ordernum',
        'postsuban',
        'status',
        'date',
        'payment_method',
        'ordermemo',
        'carriage',
        'sumprice',
        'buyer_id',
        'buyer_name',
        'buyer_kana',
        'buyer_tel',
        'buyer_tel2',
        'buyer_email',
        'buyer_zip',
        'buyer_address',
        'buyer_area',
        'buyer_city',
        'buyer_street',
        'notes',
        'usepoint',
        'price_per_tax_rate_list',
        'buyer_membergroup',
        'bulk',
        'coupon',
        'couponcode',
        'couponname',
        'old_postsuban',
        'old_sumprice',
        'old_order_date',
        'date_update',
    ];

    /**
     * Get all commodities of order
     *
     * @return Builder
     */
    public function commodities()
    {
        return $this->hasMany(Commodity::class);
    }

    /**
     * Get all deliveries of order
     *
     * @return Builder
     */
    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    /**
     * Get LINK of order
     *
     * @return Builder
     */
    public function link()
    {

        return $this->hasOne(Link::class, 'makeshop_id', 'buyer_id')
            ->SELECT('team26_id','makeshop_id','last_login_at')
            ->orderBy('last_login_at', 'DESC');
    }

    /**
     * Get tax values
     *
     * @return void
     */
    public function getTaxValuesAttribute()
    {

        $priceTotalPerTaxRate10 = $this->getPricePerTaxRate(10);
        $priceTotalPerTaxRate8 = $this->getPricePerTaxRate(8);

        $priceTaxPerRate10 = $priceTotalPerTaxRate10 > 0 ? round($priceTotalPerTaxRate10 * 10 / (100 + 10)) : 0;
        $priceTaxPerRate8 = $priceTotalPerTaxRate8 > 0 ? round($priceTotalPerTaxRate8 * 8 / (100 + 8)) : 0;
        return [
            'priceTotalPerTaxRate10' =>  $priceTotalPerTaxRate10,
            'priceTotalPerTaxRate8' =>  $priceTotalPerTaxRate8,
            'priceTaxPerRate10' =>  $priceTaxPerRate10,
            'priceTaxPerRate8' =>  $priceTaxPerRate8,
        ];
    }

    /**
     * @param $order
     * @param $rate
     * @return int
     */
    public function getPricePerTaxRate($rate)
    {
        $price = 0;
        foreach($this->commodities as $commodity) {
            if ($commodity->consumption_tax_rate == $rate) {
                $price += ($commodity->price * $commodity->amount);
            }
        }

        return $price;
    }

    /**
     * Check buyer is customer
     *
     * @return void
     */
    public function buyerIsCustomer()
    {
        return preg_match('/^\d+$/', $this->buyer_id) === 1;
    }

    /**
     * Get order record with max ID
     *
     * @return void
     */
    public function maxOrdernum()
    {
        return $this->hasOne(Order::class, 'ordernum', 'ordernum')->orderBy('id', 'DESC');
    }
}
