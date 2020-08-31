<?php

namespace App\Services\Xml\Items;

use Carbon\Carbon;
/**
 * Class F_JS
 * @package App\Services\Xml\Items
 */
class F_JS
{
    const ORDER_STATUS_PROVISIONAL_ORDER = '1111';

    /**
     * @var array
     */
    private $data;

    /**
     * F_JS constructor.
     */
    public function __construct($order, $commodities)
    {
        $this->initial();
        $this->override($order, $commodities);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function initial()
    {
        $this->data = [
            'sp' => ''
        ];
    }

    /**
     * Process data
     *
     * @param array $order
     * @param array $commodities
     * @return void
     */
    private function override($order, $commodities)
    {
        $orderDate = Carbon::parse($order['date']);
        $buyerId = $order->buyerIsCustomer() ? $order['buyer_id'] : '';
        $jancodes = [];
        foreach($commodities as $commodity) {
            if (!empty($commodity['jancode'])) {
                $jancodes[] = $commodity['jancode'];
            }
        }
        $content = [
            '店コード' => 's_journal 9',
            'グループ№' => 1,
            'ＰＯＳ№' => 1,
            '取引通番' => $order['postsuban'],
            'ＰＯＳ日付' => '#' . $orderDate->format('Ymd') . '#',
            'ＰＯＳ時間' => '#' . $orderDate->format('His') . '#',
            '取引区分' => '#' . ($order['status'] == 1 ? '0000' : ($order['status'] == 0 ? '2000' : self::ORDER_STATUS_PROVISIONAL_ORDER)) . '#',
            '担当者コード' => '99999999',
            '取引合計額' => $order['sumprice'],
            'JANコード' => '# ' . implode(' , ', $jancodes) . ' #',
            '支払条件(現外コード)' => '# 00 #',
            'カード区分' => '#0#',
            'カード№' => '##',
            '会員番号' => '#'. $buyerId .'#',
            '領収証宣言フラグ' => '#0#',
            '再発行取引フラグ' => '#0#',
            '業態コード' => '#00#',
        ];

        $this->data['sp'] = implode(',', $content);
    }
}