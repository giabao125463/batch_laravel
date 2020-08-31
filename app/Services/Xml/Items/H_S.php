<?php


namespace App\Services\Xml\Items;

use Carbon\Carbon;

/**
 * Class H_S
 * @package App\Services\Xml\Items
 */
class H_S
{
    /**
     * @var array
     */
    private $data;

    /**
     * H_S constructor.
     * @param $order
     */
    public function __construct($order)
    {
        $this->data = $this->initial();

        // init & override
        $orderDate = Carbon::parse($order['date']);
        $this->data['sSH_TrTuban'] = $order['postsuban'];
        $this->data['sSH_EigyoDate'] = $orderDate->format('Ymd');
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
    private function initial()
    {
        return [
            'iSH_MiseCode' => 9,#固定値
            'iSH_GroupNo' => 1,#固定値
            'iSH_PosNo' => 1,#固定値
            'sSH_GyotaiCode' => '00',#固定値
            'iSH_PosKbn' => 0,#固定値
            'iSH_InitFlg' => 0,#固定値
            'sSH_TrTuban' => '',# 注文番号
            'sSH_RyoTuban' => 1,#固定値
            'iSH_TrainingFlg' => 0,#固定値
            'sSH_MiseName' => 'オンラインストア',
            'sSH_PluZokusei' => '01',#固定値
            'sSH_VL' => '',#固定値
            'iSH_TantouNo' => 99999999,#固定値
            'sSH_TantouMei' => 'オンラインストア',
            'iSH_TantouLevel' => 1,#固定値
            'sSH_TantouPass' => '',#固定値
            'sSH_EigyoDate' => '', #注文日
            'sSH_HanbaiMei' => 'オンラインストア',
            'sSH_HanbaiPass' => '',#固定値
            'iSH_HanbaiNo' => 99999999,#固定値
            'iSH_HanbaiLevel' => 1,#固定値
            'sSH_SeisanNo' => '1',#固定値
            'sSH_MiketuTuban' => '1',#固定値
            'iSH_Weather' => 0,#固定値
            'iSH_PosLevel' => 0,#固定値
            'sSH_DefaultSyohinKbn' => '',#固定値
            'sSH_KameiTenPrint1' => '',#固定値
            'sSH_KameiTenPrint2' => '',#固定値
            'sSH_CreditSlipNo' => '',#固定値
            'lSH_Syuno1' => 0,#固定値
            'lSH_Syuno5' => 0,#固定値
            'lSH_Syuno10' => 0,#固定値
            'lSH_Syuno50' => 0,#固定値
            'lSH_Syuno100' => 0,#固定値
            'lSH_Syuno500' => 0,#固定値
            'lSH_Syuno1000' => 0,#固定値
            'lSH_Syuno2000' => 0,#固定値
            'lSH_Syuno5000' => 0,#固定値
            'lSH_Syuno10000' => 0,#固定値
            'lSH_Kaisyu1000' => 0,#固定値
            'lSH_Kaisyu2000' => 0,#固定値
            'lSH_Kaisyu5000' => 0,#固定値
            'lSH_Kaisyu10000' => 0,#固定値
            'sSH_MenzeiTuban' => 1,#固定値
            'sSH_HoryuTuban' => 1,#固定値
            'lSH_TsurisenJunbikin' => 0,#固定値
            'iSH_CATSeqNo' => 0,#固定値
            'iSH_KPrinterNo' => 0,#固定値
        ];
    }
}