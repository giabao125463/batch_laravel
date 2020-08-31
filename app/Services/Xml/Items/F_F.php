<?php

namespace App\Services\Xml\Items;

use App\Repositories\ItemMasterRepository;

/**
 * Class F_F
 * @package App\Services\Xml\Items
 */
class F_F
{
    /**
     * @var array
     */
    private $data;

    /**
     * F_F constructor.
     *
     * @param $order
     * @param $commodities
     * @param $itemMasters
     */
    public function __construct($order, $commodities, $itemMasters)
    {
        $initial = $this->initial();
        $this->data = $this->override($initial, $order, $commodities, $itemMasters);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get Original Price Total
     *
     * @param array $commodities
     * @param array $itemMasters
     * @return number
     */
    private function getOriginalPriceTotal($commodities, $itemMasters){
        $priceTotal = 0;

        foreach ($commodities as $commodity) {
            foreach ($itemMasters as $item) {
                if ($commodity['jancode'] == $item['scancode_new']) {
                    $priceTotal += ($item['genka'] ?? 0) * $commodity['amount'];
                    break;
                }
            }
        }

        return $priceTotal;
    }

    /**
     * @param $data
     * @param $order
     * @param $commodities
     * @param $itemMasters
     * @return array
     */
    private function override($data, $order, $commodities, $itemMasters)
    {
        $amountTotal = array_reduce($commodities, function($carry, $item) {
            $carry += $item['amount'];
            return $carry;
        }, 0);
        $sumPrice = (int)($order['sumprice'] ?? 0);

        $priceTotalPerTaxRate10 = $order->getPricePerTaxRate(10);
        $priceTotalPerTaxRate8 = $order->getPricePerTaxRate(8);

        $priceTaxPerRate10 = $priceTotalPerTaxRate10 > 0 ? round($priceTotalPerTaxRate10 * 10 / (100 + 10)) : 0;
        $priceTaxPerRate8 = $priceTotalPerTaxRate8 > 0 ? round($priceTotalPerTaxRate8 * 8 / (100 + 8)) : 0;

        $data['lF_Gkei'] = $sumPrice ;
        $data['iF_Su'] = $amountTotal ?? 0;
        $data['lF_UtizeiGkei'] = $priceTaxPerRate10 + $priceTaxPerRate8;
        $data['lF_ZeiGkei'] = $priceTaxPerRate10 + $priceTaxPerRate8;
        $data['lF_UtizeiTaisyo'] = $sumPrice ;
        $data['iF_UtizeiTensu'] = $amountTotal ?? 0;
        $data['lF_ZeigakuPtn0'] = $priceTaxPerRate10 ?? 0;
        $data['lF_ZeiTaisyoPtn0'] = $priceTotalPerTaxRate10;
        $data['lF_ZeigakuPtn3'] = $priceTaxPerRate8 ?? 0;
        $data['lF_ZeiTaisyoPtn3'] = $priceTotalPerTaxRate8;
        $data['lF_NbkGkei'] = abs((int)$order['coupon']) + abs((int)$order['bulk']);
        $data['lF_ZeiGukei'] = $priceTotalPerTaxRate10 + $priceTotalPerTaxRate8;
        $data['lF_Syokei'] = $sumPrice ;
        $data['lF_GenkinHarai'] = $sumPrice ;
        $data['lF_Azukarikin'] = $sumPrice ;
        $data['lF_ArariGkei'] = $sumPrice - $this->getOriginalPriceTotal($commodities, $itemMasters);

        return $data;
    }

    /**
     * @return array
     */
    public function initial()
    {
        return [
            'lF_SWbkTaisyoGaku' => 0, #固定値
            'iF_SWbkTaisyoTen' => 0, #固定値
            'lF_SWbkGkei' => 0, #固定値
            'iF_SWbkSyubetu' => 0, #固定値
            'iF_SWbkRitu' => 0, #固定値
            'lF_SWbkRituGaku' => 0, #固定値
            'iF_SNbkSyubetu' => 0, #固定値
            'lF_SNbkGaku' => 0, #固定値
            'lF_Gkei' => 0,
            'iF_Su' => 0,
            'lF_SotozeiGkei' => 0, #外税額合計
            'lF_UtizeiGkei' => 0,
            'lF_ZeiGkei' => 0,
            'lF_SotozeiTaisyo' => 0,
            'iF_SotozeiTensu' => 0,
            'lF_UtizeiTaisyo' => 0,
            'iF_UtizeiTensu' => 0,
            'lF_HikazeiTaisyo' => 0,
            'iF_HikazeiTensu' => 0,
            'iF_ZeiKbnPtn0' => 0, #固定値
            'dF_ZeirituPtn0' => 10, #固定値
            'lF_ZeigakuPtn0' => 0,
            'lF_ZeiTaisyoPtn0' => 0,
            'iF_ZeiKbnPtn1' => 0, #固定値
            'dF_ZeirituPtn1' => 10, #固定値
            'lF_ZeigakuPtn1' => 0,
            'lF_ZeiTaisyoPtn1' => 0,
            'iF_ZeiKbnPtn2' => 0, #固定値
            'dF_ZeirituPtn2' => 0, #固定値
            'lF_ZeigakuPtn2' => 0, #固定値
            'lF_ZeiTaisyoPtn2' => 0,
            'iF_ZeiKbnPtn3' => 0, #固定値
            'dF_ZeirituPtn3' => 8, #固定値
            'lF_ZeigakuPtn3' => 0,
            'lF_ZeiTaisyoPtn3' => 0,
            'iF_ZeiKbnPtn4' => 0, #固定値
            'dF_ZeirituPtn4' => 10, #固定値
            'lF_ZeigakuPtn4' => 0,
            'lF_ZeiTaisyoPtn4' => 0,
            'iF_ZeiKbnPtn5' => 0, #固定値
            'dF_ZeirituPtn5' => 0, #固定値
            'lF_ZeigakuPtn5' => 0, #固定値
            'lF_ZeiTaisyoPtn5' => 0,  #固定値
            'iF_ZeiKbnPtn6' => 0, #固定値
            'dF_ZeirituPtn6' => 0, #固定値
            'lF_ZeigakuPtn6' => 0, #固定値
            'lF_ZeiTaisyoPtn6' => 0,  #固定値
            'iF_ZeiKbnPtn7' => 0, #固定値
            'dF_ZeirituPtn7' => 0, #固定値
            'lF_ZeigakuPtn7' => 0, #固定値
            'lF_ZeiTaisyoPtn7' => 0, #固定値
            'iF_ZeiKbnPtn8' => 0, #固定値
            'dF_ZeirituPtn8' => 0, #固定値
            'lF_ZeigakuPtn8' => 0, #固定値
            'lF_ZeiTaisyoPtn8' => 0, #固定値
            'iF_ZeiKbnPtn9' => 0, #固定値
            'dF_ZeirituPtn9' => 0, #固定値
            'lF_ZeigakuPtn9' => 0, #固定値
            'lF_ZeiTaisyoPtn9' => 0, #固定値
            'sF_BmpMark0' => '0', #固定値
            'lF_BmpNbkGaku0' => 0, #固定値
            'iF_BmpSu0' => 0, #固定値
            'lF_BmpKakaku0' => 0, #固定値
            'sF_BmpMark1' => '0', #固定値
            'lF_BmpNbkGaku1' => 0, #固定値
            'iF_BmpSu1' => 0, #固定値
            'lF_BmpKakaku1' => 0, #固定値
            'sF_BmpMark2' => '0', #固定値
            'lF_BmpNbkGaku2' => 0, #固定値
            'iF_BmpSu2' => 0, #固定値
            'lF_BmpKakaku2' => 0, #固定値
            'sF_BmpMark3' => '0', #固定値
            'lF_BmpNbkGaku3' => 0, #固定値
            'iF_BmpSu3' => 0, #固定値
            'lF_BmpKakaku3' => 0, #固定値
            'sF_BmpMark4' => '0', #固定値
            'lF_BmpNbkGaku4' => 0, #固定値
            'iF_BmpSu4' => 0, #固定値
            'lF_BmpKakaku4' => 0, #固定値
            'sF_BmpMark5' => '0', #固定値
            'lF_BmpNbkGaku5' => 0, #固定値
            'iF_BmpSu5' => 0, #固定値
            'lF_BmpKakaku5' => 0, #固定値
            'sF_BmpMark6' => '0', #固定値
            'lF_BmpNbkGaku6' => 0, #固定値
            'iF_BmpSu6' => 0, #固定値
            'lF_BmpKakaku6' => 0, #固定値
            'sF_BmpMark7' => '0', #固定値
            'lF_BmpNbkGaku7' => 0, #固定値
            'iF_BmpSu7' => 0, #固定値
            'lF_BmpKakaku7' => 0, #固定値
            'sF_BmpMark8' => '0', #固定値
            'lF_BmpNbkGaku8' => 0, #固定値
            'iF_BmpSu8' => 0, #固定値
            'lF_BmpKakaku8' => 0, #固定値
            'lF_NbkGkei' => 0,
            'lF_ZeiGukei' => 0,
            'iF_KenMaisu' => 0, #固定値
            'lF_KenGkei' => 0, #固定値
            'lF_TuriTyouka' => 0, #固定値
            'lF_Kenkankin' => 0, #固定値
            'iF_Kyakuso' => 0, #固定値
            'lF_Syokei' => 0,
            'lF_GenkinHarai' => 0,
            'lF_Turisen' => 0, #固定値
            'lF_Azukarikin' => 0,
            'lF_Zankin' => 0, #固定値
            'iF_Ninzu' => 1, #固定値
            'lF_KenTariGkei' => 0, #固定値
            'lF_EdyGkei' => 0, #固定値
            'lF_SonotaGkeiCode9' => 0, #固定値
            'lF_KenKajoGkei' => 0, #固定値
            'lF_KenTnasiGkei' => 0, #固定値
            'lF_TekureGkei' => 0, #固定値
            'lF_SonotaGkei' => 0, #固定値
            'lF_PoRiyoGkei' => 0, #固定値
            'lF_FurikaeGkei' => 0, #固定値
            'sF_ToriBcr' => '', #固定値
            'iF_InsiFlg' => 0, #固定値
            'sF_KyakusoName' => '', #固定値
            'lF_ArariGkei' => 0,
            'lF_InshiGai' => 0, #固定値
            'iF_InsiZeiKbnPtn' => 0, #固定値
            'lF_InsiZeigakuPtn' => 0, #固定値
            'lF_InsiZeiTaisyoPtn' => 0, #固定値
            'iF_SNbkRiyu' => 0, #固定値
            'iF_SWbkRiyu' => 0, #固定値
            'iF_SNbkSyubetsu' => 0, #固定値
            'iF_SWbkSyubetsu' => 0, #固定値
            'lF_GenkinUchiTax' => 0, #固定値
            'lF_TesuryoGkei' => 0, #固定値
            'iF_TesuryoSu' => 0, #固定値
            'lF_SyahanGkei' => 0, #固定値
            'lF_PNbkGaku' => 0, #固定値
            'lF_MenzeiTaisyo' => 0, #固定値
            'iF_MenzeiTensu' => 0, #固定値
            'lF_MenzeiGkei' => 0, #固定値
            'lF_IppanGkei' => 0, #固定値
            'iF_IppanTensu' => 0, #固定値
            'lF_SyomoGkei' => 0, #固定値
            'iF_SyomoTensu' => 0, #固定値
            'lF_IppanMenzeiGkei' => 0, #固定値
            'lF_SyomoMenzeiGkei' => 0, #固定値
            'lF_SetSu' => 0, #固定値
            'lF_SetKingakuGkey' => 0, #固定値
            'lF_SetNbkGkey' => 0, #固定値
            'lF_EntryTime' => 0, #固定値
            'lF_PayTime' => 0, #固定値
            'lF_CreditGkei' => 0, #固定値
            'lF_DebitGkei' => 0, #固定値
            'sF_KeigenZeiMon0' => '', #固定値
            'sF_KeigenZeiMon1' => '', #固定値
            'sF_KeigenZeiMon2' => '', #固定値
            'sF_KeigenZeiMon3' => '', #固定値
            'sF_KeigenZeiMon4' => '', #固定値
            'sF_KeigenZeiMon5' => '', #固定値
            'sF_KeigenZeiMon6' => '', #固定値
            'sF_KeigenZeiMon7' => '', #固定値
            'sF_KeigenZeiMon8' => '', #固定値
            'sF_KeigenZeiMon9' => '', #固定値
            'sF_Keigen_JigyoCode' => '', #固定値
            'lF_SenAzukari' => 0, #固定値
            'sF_MenBmpMark0' => '', #固定値
            'iF_MenBmpSu0' => 0, #固定値
            'sF_MenBmpMark1' => '', #固定値
            'iF_MenBmpSu1' => 0, #固定値
            'sF_MenBmpMark2' => '', #固定値
            'iF_MenBmpSu2' => 0, #固定値
            'sF_MenBmpMark3' => '', #固定値
            'iF_MenBmpSu3' => 0, #固定値
            'sF_MenBmpMark4' => '', #固定値
            'iF_MenBmpSu4' => 0, #固定値
            'sF_MenBmpMark5' => '', #固定値
            'iF_MenBmpSu5' => 0, #固定値
            'sF_MenBmpMark6' => '', #固定値
            'iF_MenBmpSu6' => 0, #固定値
            'sF_MenBmpMark7' => '', #固定値
            'iF_MenBmpSu7' => 0, #固定値
            'sF_MenBmpMark8' => '', #固定値
            'iF_MenBmpSu8' => 0, #固定値
            'lF_MenSWbkRituGaku' => 0, #固定値
            'lF_MenSNbkGaku' => 0, #固定値
            'lF_MenSyokei' => 0, #固定値
            'iF_MenSu' => 0, #固定値
            'lF_MenGkei' => 0, #固定値
            'lF_WarikanGaku' => 0, #固定値
            'lF_JushokuTaisyoGaku' => 0, #固定値
            'lF_JushokuLimit' => 0, #固定値
            'iF_JushokuWbkRitsu' => 0, #固定値
            'lF_JushokuWbkGaku' => 0, #固定値
        ];
    }
}