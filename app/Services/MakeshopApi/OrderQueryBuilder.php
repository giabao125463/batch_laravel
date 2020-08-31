<?php

namespace App\Services\MakeshopApi;

use KubAT\PhpSimple\HtmlDomParser;

/**
 * Class AxApiService
 * @package App\Service
 */
class OrderQueryBuilder extends BaseQueryBuilder
{
    public function __construct()
    {
        parent::__construct();
        // TODO: Do some thing more incase needed
    }

    /**
     * 注文日時、注文番号を指定することで対象注文を選択可能です。
     *
     * @param array $params
     * @return array
     */
    public function get($params)
    {
        $config  = config('makeshop');
        $path    = data_get($config, 'api.order.paths.info');
        $default = [
            'shopid'   => data_get($config, 'api.shopid'),
            'cmd'      => 'get',
            'canceled' => data_get($config, 'api.order.default_status'),
            'token'    => data_get($config, 'api.order.token'),
        ];
        $params = array_merge($default, $params);
        $orders = $this->request($path, $params, $params);

        return $orders ?? [];
    }

    /**
     * XML Decode
     *
     * @param string $content
     * @return array
     */
    protected function xmlDecode($content)
    {
        $array = parent::xmlDecode($content);
        if (isset($array['message'])) {
            return [];
        }
        $dom    = HtmlDomParser::str_get_html($content);
        $orders = $array['order'];
        if (isset($orders['ordernum'])) {
            $orders = [$orders];
        }

        foreach ($dom->find('orders > order') as $index => $order) {
            foreach ($order->find("orderdetail > usepoint") as $tag) {
                $orders[$index]['orderdetail']['usepoint']          = $tag->attr;
                $orders[$index]['orderdetail']['usepoint']['value'] = $tag->innertext();
            }
            $orders[$index]['orderdetail']['price_per_tax_rate_list'] = [];
            foreach ($order->find("orderdetail > price_per_tax_rate_list > price_per_tax_rate") as $tag) {
                $arrayAttr                                                  = $tag->attr;
                $arrayAttr['value']                                         = $tag->innertext();
                $orders[$index]['orderdetail']['price_per_tax_rate_list'][] = $arrayAttr;
            }
            foreach ($order->find("orderdetail > commodities > commodity") as $cIndex => $commodity) {
                $orders[$index]['orderdetail']['commodities']['commodity'][$cIndex]['point'] = [];
                foreach ($commodity->find('point') as $point) {
                    $arrayPoint                                                                    = $point->attr;
                    $arrayPoint['value']                                                           = $point->innertext();
                    $orders[$index]['orderdetail']['commodities']['commodity'][$cIndex]['point'][] = $arrayPoint;
                }
            }
        }

        return $orders;
    }
}
