<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\CommodityRepository;
use App\Repositories\ItemMasterRepository;
use App\Repositories\OrderRepository;
use App\Services\Xml\DataMapping;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Class PosXmlService
 * @package App\Services
 */
class PosXmlService extends BaseService
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var CommodityRepository
     */
    protected $commodityRepository;

    /**
     * @var ItemMasterRepository
     */
    protected $itemMasterRepository;


    /**
     * PosXmlService constructor.
     *
     * @param OrderRepository $orderRepository
     * @param CommodityRepository $commodityRepository
     * @param ItemMasterRepository $itemMasterRepository
     */
    public function __construct(
        OrderRepository $orderRepository,
        CommodityRepository $commodityRepository,
        ItemMasterRepository $itemMasterRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->commodityRepository = $commodityRepository;
        $this->itemMasterRepository = $itemMasterRepository;
    }

    /**
     * Output $orders as POS format and store them to a xml local file.
     *
     * @param $orders
     * @param $fileName
     */
    public function outputXml($orders, $fileName, $writeStream)
    {
        $misingJancodes = [];

        /** @var Order $order */
        foreach ($orders as $order) {
            $dataMapping = new DataMapping();
            /** @var Collection $commodities */
            $commodities = $order->commodities;

            // Get jancodes
            $jancodes = array_map(function ($item) {
                return $item['jancode'];
            }, $commodities->toArray());

            // remove null|empty
            $jancodes = array_filter($jancodes, function ($item) {
                return !empty($item);
            });

            $itemMasters = $this->itemMasterRepository->findByJancodes($jancodes);

            // Recheck jancode 
            foreach($jancodes as $jancode) {
                $existed = false;
                foreach($itemMasters as $item) {
                    if ($item->scancode_new == $jancode) {
                        $existed = true;
                    }
                }if ($existed === false) {
                    $misingJancodes[] = $jancode;
                    Log::error('Cannot find Jancode=' . $jancode . ' Ordernum=' . $order->ordernum . ' in the item master.');
                }
            }
            // No need to generate xml if missing any jancode
            if (!empty($misingJancodes)) {
                break;
            }

            $data = [
                'H_H' => $dataMapping->getValue('H_H', $order),
                'H_S' => $dataMapping->getValue('H_S', $order),
                'M_U' => $dataMapping->getValue('M_U', $order, $commodities->toArray(), $itemMasters->toArray()),
                'F_F' => $dataMapping->getValue('F_F', $order, $commodities->toArray(), $itemMasters->toArray()),
                'F_P' => $dataMapping->getValue('F_P', $order),
                'F_J' => $dataMapping->getValue('F_J', $order, $commodities->toArray()),
                'F_JS' => $dataMapping->getValue('F_JS', $order, $commodities->toArray())
            ];

            // creating obj SimpleXMLElement
            $xml = new \SimpleXMLElement('<dtsTorihikiLog xmlns="http://tempuri.org/dtsTorihikiLog.xsd"/>');

            // function call to convert array to xml
            $this->arrayToXml($data, $xml);

            $dom = dom_import_simplexml($xml);
            $item = $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
            fwrite($writeStream, mb_convert_encoding(trim($item), 'sjis-win', 'UTF-8') . "\n");
        }

        if (!empty($misingJancodes)) {

            return false;
        }

        return true;
    }

    /**
     * @param $data
     * @param $xml_data
     */
    public function arrayToXml($data, &$xml_data)
    {
        foreach ($data as $key => $value) {
            if ($key == 'F_J' && !empty($value) && is_array($value)) {
                foreach ($value as $items) {
                    $node = $xml_data->addChild($key);
                    foreach($items as $fjKey => $fjValue) {
                        $node->addChild($fjKey, $fjValue);
                    }
                }
            } elseif (is_array($value)) {
                // Incase with key is numberic
                // Convert 'ex' => [0 => [], 1 => []]
                // <ex>[]</ex> // 0
                // <ex>[]</ex> // 1

                if (Arr::isAssoc($value) == false) {
                    foreach ($value as $key1 => $value1) {
                        $subNode = $xml_data->addChild($key);
                        $this->arrayToXml($value1, $subNode);
                    }
                } else {
                    $subNode = $xml_data->addChild($key);
                    $this->arrayToXml($value, $subNode);
                }
            } else {
                $xml_data->addChild("$key", $value);
            }
        }
    }
}