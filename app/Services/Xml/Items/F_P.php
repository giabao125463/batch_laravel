<?php


namespace App\Services\Xml\Items;

/**
 * Class F_P
 * @package App\Services\Xml\Items
 */
class F_P
{
    /**
     * @var array
     */
    private $data;

    /**
     * F_P constructor.
     *
     * @param array $order
     */
    public function __construct($order)
    {
        $this->data = $this->initial();

        // override
        $this->data['sPo_KokyakuCode'] = $order->buyerIsCustomer() ? $order['buyer_id'] : '';
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
        return [
            'lPo_ZenPo' => 0,
            'lPo_Po' => 0,
            'lPo_PoBase' => 0,
            'lPo_PoFuyo' => 0,
            'lPo_PoKijun' => 0,
            'lPo_PoTaisyo' => 0,
            'iPo_Syubetu' => 0,
            'lPo_ModosiKin' => 0,
            'lPo_ModosiPo' => 0,
            'iPo_MantenSu' => 0,
            'lPo_MantenPo' => 0,
            'iPo_Input' => 4,
            'sPo_KokyakuCode' => "",
            'sPo_Card' => "",
            'lPo_TotalPo' => 0,
            'iF_EdyFri_Suu' => 0,
            'dPo_Scbairitu' => 0,
            'lPET_ZenPo' => 0,
            'lPET_Po' => 0,
            'lPET_GenzanPo' => 0,
            'lPET_UsePo' => 0,
            'lPET_TotalPo' => 0,
            's_NewDate' => "",
            's_UpDate' => "",
            'sPET_Card' => "",
            'sPET_Print' => "",
            'dPo_PoirituTaisyo' => 0,
            'sPo_Rank' => 0,
            'dPo_Rankbairitu' => 1,
            'lPo_ScGaku' => 0,
            'iPET_WriteErr' => 0,
            'lPET_HakkoPo' => 0,
            'lPo_Rank0' => 0,
            'lPo_Rank1' => 0,
            'lPo_Rank2' => 0,
            'lPo_Rank3' => 0,
            'lPo_Rank4' => 0,
            'lPo_Rank5' => 0,
            'lPo_GiftPoKijun' => 0,
            'lPo_GiftPoTaisyo' => 0,
            'lPo_GenBasis' => 100,
            'lPo_GiftBasis' => 100,
            'dPo_GiftPoirituTaisyo' => 0,
            'sPo_MantenLimitDate' => "",
            'iPo_CardBairitu' => 0,
            'lPo_GenBasisP' => 1,
            'lPo_GiftBasisP' => 1,
            'iPo_KaiAutoNbkCode' => 0,
            'sPo_CPID' => 0,
            'sPo_CardLimitDate' => "",
            'iPo_CardDuesFlg' => 0,
            'lPo_CardDuesGaku' => 0,
            'iPo_CardNbkSyubetu' => 0,
        ];
    }
}