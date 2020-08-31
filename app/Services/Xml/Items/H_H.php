<?php


namespace App\Services\Xml\Items;

use Carbon\Carbon;

/**
 * Class H_H
 * @package App\Services\Xml\Items
 */
class H_H
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $weekdays = ['月', '火', '水', '木', '金', '土', '日'];

    /**
     * 0：キャンセル
     * 1：通常
     * 99：仮注文（※1）
     */
    const TrKbn_STATUS_PROVISIONAL_ORDER = '1111';
    const MotoTuban_STATUS_PROVISIONAL_ORDER = '1111';
    const MotoPosNo_STATUS_PROVISIONAL_ORDER = '1111';
    const MotoMiseCode_STATUS_PROVISIONAL_ORDER = '1111';
    const MotoGokei_STATUS_CANCEL = -1;
    const MotoGokei_STATUS_PROVISIONAL_ORDER = -2;
    const MotoEigyoDate_STATUS_CANCEL = -1;
    const MotoEigyoDate_STATUS_PROVISIONAL_ORDER = -2;

    /**
     * H_H constructor.
     *
     * @param $order
     */
    public function __construct($order)
    {
        $data = $this->initial();
        $this->data = $this->override($data, $order);
    }

    /**
     * @param $data
     * @param $order
     * @return array
     */
    private function override($data, $order)
    {
        // init & override
        $isCancelled = $order['status'] == 0 ? true : false;
        $orderDate = Carbon::parse($order['date']);
        $weekdayName = $this->weekdays[$orderDate->dayOfWeekIso - 1];

        $data['sTH_TrKbn'] = $order['status'] == 1 ? '0000' : ($order['status'] == 0 ? '2000' : self::TrKbn_STATUS_PROVISIONAL_ORDER);
        $data['sTH_EigyoDate'] = $orderDate->format('Ymd');
        $data['sTH_PosDate'] = $orderDate->format('Ymd');
        $data['sTH_PosTime'] = $orderDate->format('His');
        $data['sTH_PosYoubi'] = $weekdayName;
        $data['sTH_MotoTuban'] = $order['status'] == 1 ? 0 : ($order['status'] == 0 ? 0 : self::MotoTuban_STATUS_PROVISIONAL_ORDER);
        $data['lTH_MotoGokei'] = $order['status'] == 1 ? 0 : ($order['status'] == 0 ? 0 : self::MotoGokei_STATUS_PROVISIONAL_ORDER);
        $data['sTH_KokyakuCode'] = $order->buyerIsCustomer() ? $order['buyer_id'] : '';
        $data['iTH_MotoPosNo'] = $order['status'] == 1 ? 0 : ($order['status'] == 0 ? 1 : self::MotoPosNo_STATUS_PROVISIONAL_ORDER);
        $data['sTH_EigyouYoubi'] = $weekdayName;
        $data['iTH_MotoMiseCode'] = $order['status'] == 1 ? 0 : ($order['status'] == 0 ? 9 : self::MotoMiseCode_STATUS_PROVISIONAL_ORDER);
        $data['sTH_MotoEigyoDate'] = $order['status'] == 1 ? 0 : ($order['status'] == 0 ? 0 : self::MotoEigyoDate_STATUS_PROVISIONAL_ORDER);
        $data['sTH_StartTime'] = $orderDate->format('YmdHis');
        $data['sTH_TrTuban'] = $order['postsuban'];

        return $data;
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
            'iTH_MiseCode' => 9,#固定値
            'iTH_GroupNo' => 1,#固定値
            'iTH_PosNo' => 1,#固定値
            'sTH_TrTuban' => '',#固定値
            'sTH_RyoTuban' => '1',#固定値
            'sTH_MiketsuTuban' => '1',#固定値
            'sTH_GyotaiCode' => '00',#固定値
            'iTH_PosKbn' => 1,#固定値
            'sTH_TrKbn' => '0000',
            'iTH_SWbkFlg' => 0,#固定値
            'iTH_GengaiFlg' => 0,#固定値
            'iTH_PoFlg' => 0,#固定値
            'iTH_CreFlg' => 0,#固定値
            'iTH_CreditFlg' => 0,#固定値
            'iTH_DebitFlg' => 0,#固定値
            'iTH_EdyFlg' => 0,#固定値
            'sTH_EigyoDate' => '',#注文日
            'sTH_PosDate' => '',#注文日
            'sTH_PosTime' => '',#注文時刻
            'sTH_PosYoubi' => '',#注文曜日
            'iTH_TantoNo' => 99999999,#固定値
            'sTH_TantoMei' => 'オンラインストア',
            'iTH_TantoLevel' => 1,#固定値
            'sTH_MotoTuban' => 0,#通常：0  キャンセル注文：注文番号
            'lTH_MotoGokei' => 0,# 通常：0  キャンセル注文：元注文の合計金額
            'iTH_ChusiFlg' => 0,#固定値
            'iTH_RyosyuChkFlg' => 0,#固定値
            'iTH_TorikesiFlg' => 0,#固定値
            'iTH_RyosyuFlg' => 0,#固定値
            'iTH_TorikesiChkFlg' => 0,#固定値
            'sTH_KokyakuCode' => '',#会員番号  未登録時は空
            'iTH_HanbaiNo' => 99999999,#固定値
            'sTH_HanbaiMei' => 'オンラインストア',
            'iTH_HanbaiLevel' => 1,#固定値
            'iTH_SaihakkouFlg' => 0,#固定値
            'iTH_SaihakkouChkFlg' => 0,#固定値
            'sTH_RsvFlg' => 0,#固定値
            'iTH_SaihakkouUmuFlg' => 0,#固定値
            'iTH_KokyakuOffLine' => 0,#固定値
            'iTH_KakakuTekiyouFlg' => 0,#固定値
            'iTH_SokuRyosyuFlg' => 0,#固定値
            'iTH_MotoPosNo' => 0,# 通常時：0 キャンセル注文：1
            'iTH_ToriMemoFlg' => 0,#固定値
            'sTH_SeisanNo' => '0',#固定値
            'iTH_MT_Keijouflg' => 0,#固定値
            'iTH_MiseGrp' => 3001,#固定値
            'sTH_KokyakuName' => '',# 固定値
            'iTH_ReportFlg' => 0,#固定値
            'iTH_PETCardShiyou' => 0,#固定値
            'sTH_PETKokyakuCode' => '',#固定値
            'sTH_EigyouYoubi' => '',# 注文曜日
            'iTH_KokyakuKbn' => 0,#固定値
            'sTH_TelNo' => '',#固定値
            'iTH_KakeFlg' => 0,#固定値
            'iTH_TrHenpinFlg' => 0,#固定値
            'iTH_MotoMiseCode' => 0,# 通常：0  キャンセル注文:9
            'sTH_MotoEigyoDate' => 0,#通常：0  キャンセル注文:元注文の注文日
            'iTH_Weather' => 0,#固定値
            'sTH_SyainCode' => '0',#固定値
            'iTH_SyahanSecLevel' => 0,#固定値
            'iTH_SyahanFlg' => 0,#固定値
            'iTH_ToriKeshiHandanFlg' => 0,#固定値
            'iTH_WrappingNo' => 0,#固定値
            'iTH_WrappingNoFlg' => 0,#固定値
            'iTH_SyokkenFlg' => 0,#固定値
            'iTH_JobCode' => 0,#固定値
            'iTH_KyakusuFlg' => 0,#固定値
            'iTH_SlipHereKbn' => 0,#固定値
            'iTH_SlipDateFlg' => 0,#固定値
            'iTH_SlipFlg' => 0,#固定値
            'iTH_PosLevel' => 0,#固定値
            'iTH_PT_FuyoTimingFlg' => 0,#固定値
            'iTH_Kyaku_PointJogai' => 0,#固定値
            'iTH_SyudoRyosyuFlg' => 0,#固定値
            'iTH_MenzeiFlg' => 0,#固定値
            'sTH_PassportNumber' => '',#固定値
            'iTH_ExeType' => 0,#固定値
            'sTH_StartTime' => '',#yyyyMMddHHmmss
            'iTH_HaikiHandanFlg' => 0,#固定値
            'iTH_PoSysFlg' => 0,#固定値
            'iTH_RaitenFlg' => 0,#固定値
            'sTH_MenzeiTuban' => '1',#固定値
            'sTH_HoryuTuban' => '1',#固定値
            'iTH_CallNo' => 1,#固定値
            'iTH_OrderingNo' => 0,#固定値
            'sTH_FullfillHeader' => '',#固定値
            'lTH_KeisuEntGaku' => 0,#固定値
            'iTH_PointPlusUseFlg' => 0,#固定値
            'iTH_ValuePayFlg' => 0,#固定値
            'iTH_PPRecoveryFlg' => 0,#固定値
            'iTH_PPCardInputType' => 0,#固定値
            'sTH_PPCardInfo' => '',#固定値
            'iTH_TokutyuFlg' => 0,#固定値
            'iH_OrderPrintCnt' => 0,#固定値
            'iTH_JushokuFlg' => 0,#固定値
        ];
    }
}