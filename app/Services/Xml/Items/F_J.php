<?php

namespace App\Services\Xml\Items;

use App\Helpers\StringHelper;
use App\Traits\XmlTrait;
use Carbon\Carbon;
/**
 * Class F_J
 * @package App\Services\Xml\Items
 */
class F_J
{
    use XmlTrait;

    /**
     * @var array
     */
    private $data;
    private $billWidth;
    private $weekdays = ['月', '火', '水', '木', '金', '土', '日'];

    /**
     * F_J constructor.
     */
    public function __construct($order, $commodities)
    {
        $this->handle($order, $commodities);
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
    public function handle($order, $commodities)
    {
        $taxTotal = $order->taxValues['priceTaxPerRate10'] + $order->taxValues['priceTaxPerRate8'];
        $orderDate = Carbon::parse($order['date']);
        $dayOfWeek = $this->weekdays[$orderDate->dayOfWeekIso - 1];
        $data = [];
        $index = 1;
        $this->billWidth = (int) config('pos.bill_width', 40);
        $data[] = [
            'id' => $index++,
            'data' => $this->arrangeCenter($order->status != 0 ? '＜オンラインストア＞' : '＜オンラインストア（取消）＞'),
        ];
        $data[] = [
            'id' => $index++,
            'data' => $orderDate->format('Y年m月d日（' . $dayOfWeek . '）H時i分'),
        ];
        $data[] = [
            'id' => $index++,
            'data' => 'オンラインストア',
        ];
        $data[] = [
            'id' => $index++,
            'data' => $order['ordernum'],
        ];
        foreach($commodities as $item) {
            $amount = 'x' . number_format($item['amount'], 0);
            $price = number_format($item['price'] * $item['amount'], 0);
            $txtAmount = $this->leftSpacesGen($amount, 5);
            $txtPrice = $this->leftSpacesGen($price, 15);
            $name = $this->removeSpecialCharacters($item['name']);
            $maxStr = "{$item['jancode']}:{$name}{$txtAmount}{$txtPrice}";
            if (StringHelper::mbStringWidth($maxStr) >= $this->billWidth) {
                $data[] = [
                    'id' => $index++,
                    'data' => $this->arrange2Col("{$item['jancode']}:{$name}", ''),
                ];
                $data[] = [
                    'id' => $index++,
                    'data' => $this->arrange2Col('', "{$txtAmount}{$txtPrice}"),
                ];
            } else {
                $text = $this->arrange2Col("{$item['jancode']}:{$name}", "{$txtAmount}{$txtPrice}");
                $data[] = [
                    'id' => $index++,
                    'data' => $text,
                ];
            }
        }
        $data[] = [
            'id' => $index++,
            'data' => '----------------------------------------',
        ];
        $data[] = [
            'id' => $index++,
            'data' => $this->arrange2Col('小計 ', number_format($order['sumprice'], 0)),
        ];
        $data[] = [
            'id' => $index++,
            'data' => $this->arrange2Col('消費税 ', number_format($taxTotal, 0)),
        ];
        $data[] = [
            'id' => $index++,
            'data' => $this->arrange2Col('合計 ', number_format($order['sumprice'], 0)),
        ];
        $this->data = $data;
    }

    /**
     * Arrange text left - right
     *
     * @param string $left
     * @param string $right
     * @return string
     */
    private function arrange2Col($left, $right)
    {
        $leftW = StringHelper::mbStringWidth($left);
        if ($leftW > $this->billWidth) {
            $left = StringHelper::subString($left, $this->billWidth);
        }
        $rightW = StringHelper::mbStringWidth($right);
        $spaces = '';
        for($i = 0; $i < $this->billWidth - ($leftW + $rightW); $i++) {
            $spaces .= ' ';
        }
        return $left . $spaces . $right;
    }

        /**
     * Arrange text left - right
     *
     * @param string $left
     * @param string $right
     * @return string
     */
    private function arrangeCenter($text)
    {
        $len = (int) (($this->billWidth - StringHelper::mbStringWidth($text)) / 2);
        $lastSpace   = ($this->billWidth != ($len * 2 + StringHelper::mbStringWidth($text))) ? ' ' : '';
        $spaces = '';
        for($i = 0; $i < $len; $i++) {
            $spaces .= ' ';
        }
        return $spaces . $text . $spaces . $lastSpace;
    }

    /**
     * Generate left space
     *
     * @param string $text
     * @param int $width
     * @return string
     */
    private function leftSpacesGen($text, $width)
    {
        $spaces = '';
        for ($i = 0; $i < $width - StringHelper::mbStringWidth($text); $i++) {
            $spaces .= ' ';
        }

        return $spaces . $text;
    }
}