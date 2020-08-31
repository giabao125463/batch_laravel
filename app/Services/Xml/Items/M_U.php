<?php

namespace App\Services\Xml\Items;

use App\Models\Order;
use App\Traits\XmlTrait;

use function foo\func;

/**
 * Class M_U
 * @package App\Services\Xml\Items
 */
class M_U
{
    use XmlTrait;
    /**
     * @var array
     */
    private $data;

    /**
     * @var
     */
    private $order;

    /**
     * @var array
     */
    private $taxes = [];

    /**
     * @var array
     */
    private $itemMasters = [];

    /**
     * M_U constructor.
     * @param $order
     * @param $commodities
     * @param $itemMasters
     */
    public function __construct($order, $commodities, $itemMasters)
    {
        $this->data = [];
        $this->order = $order;
        $this->itemMasters = $itemMasters;

        $initial = $this->initial();
        $numberNo = 1;

        // 消費税率 (8%) && 消費税率 (10%)
        $this->taxes = $this->getTaxPriceList($commodities, 8) + $this->getTaxPriceList($commodities, 10);

        foreach ($commodities as $commodity) {
            $this->data[] = $this->override($initial, $commodity, $numberNo++);
        }
    }

    /**
     * @param $commodities
     * @param $rate
     * @return array
     */
    private function getTaxPriceList($commodities, $rate)
    {
        $taxes = [];

        $items = array_filter($commodities, function ($item) use ($rate) {
            return $item['consumption_tax_rate'] == $rate;
        });
        $priceTotal = array_reduce($items, function ($carry, $item) {
            $carry += ($item['price'] * $item['amount']);
            return $carry;
        }, 0);
        $includedTaxTotal = round($priceTotal * $rate / (100 + $rate));

        foreach ($items as $item) {
            $taxes[$item['jancode']] = round($includedTaxTotal * $item['price'] * $item['amount'] / $priceTotal);

            $includedTaxTotal -= $taxes[$item['jancode']];
            $priceTotal -= ($item['price'] * $item['amount']);
        }

        return $taxes;
    }

    /**
     * @param $jancode
     * @return array
     */
    private function getItemMaster($jancode)
    {
        $result = [];

        foreach ($this->itemMasters as $item) {
            if ($item['scancode_new'] == $jancode) {
                $result = $item;
                break;
            }
        }
        return $result;
    }

    /**
     * @param $data
     * @param $commodity
     * @param $numberNo
     *
     * @return array
     */
    private function override($data, $commodity, $numberNo)
    {
        $itemMaster = $this->getItemMaster($commodity['jancode']);

        $data['iM_Meino'] = $numberNo;
        $data['iM_Bumon'] = (int) ($itemMaster['bumon'] ?? 0);
        $data['iM_Hinsyu'] = (int) ($itemMaster['hinsyu'] ?? 0);
        $data['sM_Hinmei'] = $this->fillWidthWithSpace($commodity['name'], 40);
        $data['sM_Hinmei_Kana'] = !empty($itemMaster) ? $itemMaster['hinmei_kana'] : '';
        $data['iM_ZeiPtn'] = $commodity['consumption_tax_rate'] == 8 ? 2 : ($commodity['consumption_tax_rate'] == 10 ? 0 : -1);
        $data['dM_Zeiritu'] = (int) ($commodity['consumption_tax_rate'] ?? 0);

        if (!empty($this->taxes[$commodity['jancode']])) {
            $data['lM_Utizeigaku'] = $this->taxes[$commodity['jancode']] ?? 0;
        }

        $data['iM_Suryo'] = (int) ($commodity['amount'] ?? 0);
        $data['lM_Tanka'] = (int) ($commodity['price'] ?? 0);

        $data['lM_GokaNbkMae'] = (int) ($data['iM_Suryo'] * $data['lM_Tanka']);
        $data['lM_GokaNbkGo'] = $data['lM_GokaNbkMae'];
        $data['lM_Goka'] = $data['lM_GokaNbkMae'];
        $data['lM_UriGoka'] = $data['lM_GokaNbkMae'];

        $data['lM_HyojunBaika'] = (int) ($commodity['price'] ?? 0);
        $data['sM_Scancode1'] = $commodity['jancode'];

        $data['lM_Ararigaku'] = (int)(($commodity['price'] * $commodity['amount']) - (($itemMaster['genka'] ?? 0) * $commodity['amount']));
        $data['sM_SeisansyaCode'] = !empty($itemMaster) ? $itemMaster['seisansyacode'] : '';

        $commodityPrice = (int) ($commodity['price'] ?? 0);
        $data['lM_MstSTanka1'] = $commodityPrice;
        $data['lM_MstHBaika2'] = $commodityPrice;
        $data['lM_MstHontaiKakaku'] = $commodityPrice;
        $data['lM_MstGenka'] = $itemMaster['genka'] ?? 0;

        $data['sM_MstPLUCode'] = $commodity['jancode'];
        $data['sM_MstItemCode'] = $commodity['jancode'];

        $data['iM_TpnGpNo1'] = (int) ($itemMaster['tanpingroupno1'] ?? 0);
        $data['iM_TpnGpNo2'] = (int) ($itemMaster['tanpingroupno2'] ?? 0);

        $data['dM_BulkSu'] = (int) ($commodity['amount'] ?? 0);
        $data['lM_BulkTanka'] = $commodityPrice;
        $data['lM_MotoMstSTanka1'] = $commodityPrice;
        $data['lM_MotoMstHBaika1'] = $commodityPrice;
        $data['lM_MotoHyojunBaika'] = $commodityPrice;
        $data['lM_MotoBulkTanka'] = $commodityPrice;

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
            'iM_Meino' => 1,
            'iM_Input' => 1,#固定値
            'iM_PoJFlg' => 0,#固定値
            'iM_UriJFlg' => 0,#固定値
            'iM_BaihenFlg' => 0,#固定値
            'iM_MatomeFlg' => 0,#固定値
            'iM_BarukuFlg' => 0,#固定値
            'iM_TeiseiFlg' => 0,#固定値
            'iM_HenbinFlg' => 0,#固定値
            'iM_MinusFlg' => 0,#固定値
            'sM_TourokuFlg' => '0',#固定値
            'iM_Group' => 0,#固定値
            'iM_Bumon' => '',#部門コード
            'iM_Hinsyu' => '',#小分類コード
            'sM_Hinmei' => '',
            'sM_Hinmei_Kana' => '',# 商品名ｶﾅ（半角）
            'iM_ZeiKbn' => 0,#固定値
            'iM_ZeiPtn' => 0,
            'sM_ZeiMon' => '内',#内税：内, 外税：外, 非課税：非
            'dM_Zeiritu' => '',#消費税率
            'lM_Sotozeigaku' => 0,#固定値
            'lM_Utizeigaku' => 0,#取引合計の内税額を商品毎に按分した税額
            'iM_FullBmnNo' => 0,#固定値
            'iM_Suryo' => 0,
            'lM_Tanka' => 0,
            'lM_GokaNbkMae' => 0,
            'lM_GokaNbkGo' => 0,
            'lM_Goka' => 0,
            'lM_UriGoka' => 0,
            'lM_HyojunBaika' => 0,
            'sM_Scancode1' => '',
            'sM_Scancode2' => '',#固定値
            'sM_PluZokusei' => '1',#固定値
            'iM_BmpFlg' => 0,#固定値
            'iM_TokubaiFlg' => 0,#固定値
            'iM_TimeFlg' => 0,#固定値
            'iM_NbkWbkJFlg' => 0,#固定値
            'iM_NbkFlg' => 0,#固定値
            'iM_NmsFlg' => 0,#固定値
            'iM_TpnNWFlg' => 0,#固定値
            'iM_AutoNWFlg' => 0,#固定値
            'iM_AutoTimeFlg' => 0,#固定値
            'iM_SWbkFlg' => 0,#固定値
            'iM_SWmsFlg' => 0,#固定値
            'iM_BaihenDFlg' => 0,#固定値
            'iM_BaihenUFlg' => 0,#固定値
            'iM_SetFlg' => 0,#固定値
            'iM_KaiinNbkFlg' => 0,#固定値
            'iM_SyainNbkFlg' => 0,#固定値
            'iM_TokusyuNbkFlg' => 0,#固定値
            'sM_SeirituFlg' => 0,#固定値
            'sM_NbkWbkJMon' => '',#固定値
            'lM_TokubaiNbkG' => 0,#固定値
            'sM_TokubaiMon' => '',#固定値
            'iM_BmpNo' => 0,#固定値
            'sM_BmpMark' => '0',#固定値
            'lM_BmpNbkGaku' => 0,#固定値
            'iM_SetNo' => 0,#固定値
            'iM_SetTen' => 0,#固定値
            'lM_SetGaku' => 0,#固定値
            'lM_SetNbkGaku' => 0,#固定値
            'iM_WbkSyubetu' => 0,#固定値
            'iM_WbkRitu' => 0,#固定値
            'lM_WbkRituGaku' => 0,#固定値
            'iM_NbkSyubetu' => 0,#固定値
            'lM_NbkGaku' => 0,#固定値
            'lM_NbkGakukei' => 0,#固定値
            'lM_NbkGoukei' => 0,#固定値
            'iM_TpnNbkSbt' => 0,#固定値
            'iM_TpnNbkRitu' => 0,#固定値
            'lM_TpnNbkRituGaku' => 0,#固定値
            'lM_TpnNbkGakukei' => 0,#固定値
            'iM_BaihenSyubetu' => 0,#固定値
            'lM_BaihenSinBaika' => 0,#固定値
            'lM_BaihenOldBaika' => 0,#固定値
            'lM_BaihenNbkGaku' => 0,#固定値
            'lM_SNbkGaku' => 0,#固定値
            'lM_SWbkGaku' => 0,#固定値
            'sM_KaiNbkMon' => '',#固定値
            'iM_KaiNbkSbt' => 0,#固定値
            'iM_KaiNbkRitu' => 0,#固定値
            'lM_KaiNbkRituGaku' => 0,#固定値
            'lM_KaiNbkGaku' => 0,#固定値
            'lM_KaiKakakuNbk' => 0,#固定値
            'iM_SyaNbkSbt' => 0,#固定値
            'iM_SyaNbkRitu' => 0,#固定値
            'lM_SyaNbkRituGaku' => 0,#固定値
            'lM_SyaNbkGaku' => 0,#固定値
            'lM_SyaKakakuNbk' => 0,#固定値
            'iM_TokuNbkSbt' => 0,#固定値
            'iM_TokuNbkRitu' => 0,#固定値
            'lM_TokuNbkRituGaku' => 0,#固定値
            'lM_TokuNbkGaku' => 0,#固定値
            'lM_TokuKakakuNbk' => 0,#固定値
            'iM_TokuNbkSbt2' => 0,#固定値
            'iM_TokuNbkRitu2' => 0,#固定値
            'lM_TokuNbkRituGaku2' => 0,#固定値
            'lM_TokuNbkGaku2' => 0,#固定値
            'lM_TokuKakakuNbk2' => 0,#固定値
            'iM_TokuNbkSbt3' => 0,#固定値
            'iM_TokuNbkRitu3' => 0,#固定値
            'lM_TokuNbkRituGaku3' => 0,#固定値
            'lM_TokuNbkGaku3' => 0,#固定値
            'lM_TokuKakakuNbk3' => 0,#固定値
            'sM_SizeCode' => '',#固定値
            'sM_ColorCode' => '',#固定値
            'iM_NbkRiyuu' => 0,#固定値
            'iM_HanbaiRiyuu' => 0,#固定値
            'sM_Rsv1' => '',#固定値
            'sM_Rsv2' => '',#固定値
            'sM_Rsv3' => '',#固定値
            'sM_Rsv4' => '',#固定値
            'sM_Rsv5' => '',#固定値
            'lM_Ararigaku' => 0,
            'sM_SeisansyaCode' => '',
            'iM_MstPoJFlg' => 0,#固定値
            'iM_MstNbkWbkJFlg' => 0,#固定値
            'iM_MstUriJFlg' => 0,#固定値
            'iM_MstMinusFlg' => 0,#固定値
            'lM_MstSTanka1' => 0,
            'lM_MstBTanka1' => 0,#固定値
            'lM_MstOTanka1' => 0,#固定値
            'lM_MstHBaika1' => 0,#固定値
            'sM_MstPrise2Date' => '',#固定値
            'lM_MstSTanka2' => 0,#固定値
            'lM_MstBTanka2' => 0,#固定値
            'lM_MstOTanka2' => 0,#固定値
            'lM_MstHBaika2' => 0,
            'lM_MstHontaiKakaku' => 0,
            'lM_MstGenka' => '',
            'iM_MstTpnNWFlg' => 0,#固定値
            'lM_MstTpnNWGaku' => 0,#固定値
            'iM_MstKaiFlg' => 0,#固定値
            'lM_MstKaiBaika' => 0,#固定値
            'iM_MstSyaFlg' => 0,#固定値
            'lM_MstSyaBaika' => 0,#固定値
            'iM_MstTokuFlg' => 0,#固定値
            'lM_MstTokuBaika' => 0,#固定値
            'iM_MstTokuFlg2' => 0,#固定値
            'lM_MstTokuBaika2' => 0,#固定値
            'iM_MstTokuFlg3' => 0,#固定値
            'lM_MstTokuBaika3' => 0,#固定値
            'iM_MstTokubaiFlg' => 0,#固定値
            'iM_MstTokubaiSyubetu' => 3,#固定値
            'lM_MstTokubaiSetting' => 0,#固定値
            'iM_MstKainTokuFlg' => 0,#固定値
            'lM_MstKainTokuSetting' => 0,#固定値
            'iM_MstTimeFlg' => 0,#固定値
            'iM_MstTimeTokuSyubetu' => 3,#固定値
            'iM_MstKainTimeFlg' => 0,#固定値
            'sM_MstTimeStart' => '0',#固定値
            'sM_MstTimeEnd' => '0',#固定値
            'lM_MstTimeTokuSetting' => 0,#固定値
            'lM_MstKainTimeSetting' => 0,#固定値
            'lM_MstTimeBaika' => 0,#固定値
            'lM_MstTokubaiNebiki' => 0,#固定値
            'lM_MstTokubaiWaribiki' => 0,#固定値
            'lM_MstTokubaiWaribikiGaku' => 0,#固定値
            'lM_MstTokubaiKakaku' => 0,#固定値
            'lM_MstTimeTokubaiNebiki' => 0,#固定値
            'lM_MstTimeTokubaiWaribiki' => 0,#固定値
            'lM_MstTimeTokubaiWaribikiGaku' => 0,#固定値
            'lM_MstTimeTokubaiKakaku' => 0,#固定値
            'iM_MstBmpFlg' => 0,#固定値
            'iM_MstBmpNo' => 0,#固定値
            'sM_MstBmpStartTime' => '',#固定値
            'sM_MstBmpEndTime' => '',#固定値
            'iM_MstBmpPkgSuryo' => 0,#固定値
            'lM_MstBmpPkgGaku' => 0,#固定値
            'iM_MstBmpPkgSuryo2' => 0,#固定値
            'lM_MstBmpPkgGaku2' => 0,#固定値
            'iM_BmSeiritsugoGakuFlg' => 0,#固定値
            'lM_BmSeiritsugoGaku' => 0,#固定値
            'iM_MstKaiinBmpPkgSuryo' => 0,#固定値
            'lM_MstKaiinBmpPkgGaku' => 0,#固定値
            'iM_MstKaiinBmpPkgSuryo2' => 0,#固定値
            'lM_MstKaiinBmpPkgGaku2' => 0,#固定値
            'iM_KaiinBmSeiritsugoGakuFlg' => 0,#固定値
            'lM_KaiinBmSeiritsugoGaku' => 0,#固定値
            'iM_MstKumiKbn' => 0,#固定値
            'iM_MstPairMeiNum' => 0,#固定値
            'lM_MotoMstKaiinBmpPkgGaku' => 0,#固定値
            'lM_MotoMstKaiinBmpPkgGaku2' => 0,#固定値
            'lM_MotoBmKaiinSeiritsugoGaku' => 0,#固定値
            'sM_MstHanbaiFlg' => '0',#固定値
            'sM_MstInziFlg' => '0',#固定値
            'iM_MstZeiKbn' => 0,#固定値
            'iM_MstZeiPtn' => 0,#固定値
            'sM_MstPLUCode' => '',
            'iM_KakuteiFlg' => 1,#固定値
            'iM_JotaiFlg' => 0,#固定値
            'sM_MstItemCode' => '',
            'iM_TpnGpNo1' => '',
            'iM_TpnGpNo2' => '',
            'lM_MstRsv1' => 13,#固定値
            'lM_MstRsv2' => 0,#固定値
            'lM_MstRsv3' => 0,#固定値
            'lM_MstRsv4' => 0,#固定値
            'lM_MstRsv5' => 0,#固定値
            'sM_MstSetumei' => '',#固定値
            'iM_InpKakakuFlg' => 1,#固定値
            'iM_BaikaKyoseiFlg' => 0,#固定値
            'iM_MHaseiFlg' => 0,#固定値
            'lM_MUtikinNbkGo' => 0,#固定値
            'lM_MUtikinGoka' => 0,#固定値
            'lM_MUtikinSotozei' => 0,#固定値
            'lM_MUtikinUtizei' => 0,#固定値
            'lM_MZankinNbkGo' => 0,#固定値
            'lM_MZankinGoka' => 0,#固定値
            'lM_MZankinSotozei' => 0,#固定値
            'lM_MZankinUtizei' => 0,#固定値
            'dM_Poritu' => 0,#固定値
            'lM_Po' => 0,#固定値
            'iM_hoshouFlg' => 0,#固定値
            'iM_MstBallSu' => 0,#固定値
            'lM_MstBallTanka' => 0,#固定値
            'iM_MstCaseSu' => 0,#固定値
            'lM_MstCaseTanka' => 0,#固定値
            'iM_SuryoFlg' => 0,#固定値
            'iM_PoFlg' => 0,#固定値
            'sM_HosyouMongon' => '',#固定値
            'iM_GiftNo' => 0,#固定値
            'sM_Tanburitu' => '',#固定値
            'iM_SentakuFlg' => 0,#固定値
            'iM_MstAutoFlg' => 0,#固定値
            'lM_MstAutoRituGaku' => 0,#固定値
            'iM_MstAutoKbn' => 0,#固定値
            'iM_MstAutoTimeFlg' => 0,#固定値
            'sM_MstAutoTimeStart' => '',#固定値
            'sM_MstAutoTimeEnd' => '',#固定値
            'iM_MstAutoNbkSbt' => 0,#固定値
            'iM_AutoNbkRitu' => 0,#固定値
            'lM_AutoNbkRituGaku' => 0,#固定値
            'lM_AutoNbkGakuKei' => 0,#固定値
            'iM_MstKikakuCode' => 0,#固定値
            'iM_BaikaRiyu' => 0,#固定値
            'iM_NbkRiyu' => 0,#固定値
            'iM_WbkRiyu' => 0,#固定値
            'iM_NbkSyubetsu' => 0,#固定値
            'iM_WbkSyubetsu' => 0,#固定値
            'sM_Minus_Mon' => '',#固定値
            'iM_TesuryoFlg' => 0,#固定値
            'dM_TesuryoRitsu' => 0,#固定値
            'sM_MstItemCodeOrg' => '',#固定値
            'sM_MstItemBunrui' => '0',#固定値
            'iM_HanSyoFlg' => 0,#固定値
            'iM_HanSyoAutoFlg' => 0,#固定値
            'iM_HanSyoPtn' => 0,#固定値
            'iM_HanSyoKikan' => 0,#固定値
            'iM_HanSyoMaisu' => 0,#固定値
            'iM_ManNbkJFlg' => 0,#固定値
            'iM_HoSyoFlg' => 0,#固定値
            'iM_HoSyoAutoFlg' => 0,#固定値
            'iM_HoSyoPtn' => 0,#固定値
            'iM_HoSyoKikan' => 0,#固定値
            'iM_HoSyoMaisu' => 0,#固定値
            'iM_TakeOutFlg' => 0,#固定値
            'iM_SyokkenFlg' => 0,#固定値
            'sM_TakeOut_Mon' => '',#固定値
            'iM_EmgEntryFlg' => 0,#固定値
            'sM_HostHinmei' => '',#固定値
            'sM_KikakuMei' => '',#固定値
            'iM_ItemCodeEntFlg' => 0,#固定値
            'iM_BulkFlg' => 0,#固定値
            'dM_BulkSu' => 0,
            'lM_BulkTanka' => 0,
            'sM_BulkMark' => '',#固定値
            'sM_PrtBulkSu' => '',#固定値
            'iM_MenzeiKbn' => 0,#固定値
            'sM_MenzeiJogaiMark' => '',#固定値
            'lM_Menzeigaku' => 0,#固定値
            'lM_MotoMstSTanka1' => '',
            'lM_MotoMstHBaika1' => '',
            'lM_MotoHyojunBaika' => '',
            'lM_MotoBulkTanka' => '',
            'lM_MotoMstTimeBaika' => 0,#固定値
            'iM_BmpSeirituFlg' => 0,#固定値
            'lM_MotoMstBmpPkgGaku' => 0,#固定値
            'lM_MotoMstBmpPkgGaku2' => 0,#固定値
            'lM_MotoBmSeiritsugoGaku' => 0,#固定値
            'iM_MeisaiMenzeiFlg' => 0,#固定値
            'iM_SetSu' => 0,#固定値
            'sM_SetCode' => '',#固定値
            'sM_SetName' => '',#固定値
            'lM_MenzeiMaeSetGaku' => 0,#固定値
            'lM_AllSetNbkGaku' => 0,#固定値
            'lM_AllSetNbkGkey' => 0,#固定値
            'iM_ScanFlg' => 0,#固定値
            'iM_hanbaiNGFlg' => 0,#固定値
            'sM_KeigenZeiMon' => 0,#固定値
            'iM_SyokkenPrinterNo' => 0,#固定値
            'iM_DivMeino' => 0,
            'iM_Ccode' => 0,
            'sM_ZassiCode' => 0,#固定値
            'iM_ZeihenJoflg' => 0,#固定値
            'iM_Zeihenflg' => 0,#固定値
            'iM_MotoZeiPtn' => 0,#固定値
            'lM_ZeihenMaeSetGaku' => 0,#固定値
            'iM_KeiryoFlg' => 0,#固定値
            'lM_RealWeight' => 0,#固定値
            'lM_HutaiWeight' => 0,#固定値
            'iM_HutaiSuryo' => 0,#固定値
            'lM_KeiryoTanka' => 0,#固定値
            'iM_ItemKbn' => 0,#固定値
            'iM_BarcodeKbn' => 0,#固定値
            'iM_ToppingFlg' => 0,#固定値
            'iM_ToppingParentNo' => 0,#固定値
            'iM_ToppingChildNo' => 0,#固定値
            'sM_ToppingMark' => 0,#固定値
            'iM_OrderPrintFlg' => 0,#固定値
            'sM_OrderHinmei' => 0,#固定値
            'iM_JushokuFlg' => 0,#固定値
            'lM_JushokuWbkGaku' => 0,#固定値
        ];
    }
}