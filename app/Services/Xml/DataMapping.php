<?php

namespace App\Services\Xml;

use App\Services\Xml\Items\F_F;
use App\Services\Xml\Items\F_J;
use App\Services\Xml\Items\F_JS;
use App\Services\Xml\Items\F_P;
use App\Services\Xml\Items\H_H;
use App\Services\Xml\Items\H_S;
use App\Services\Xml\Items\M_U;

class DataMapping
{
    /**
     *
     * @param string $type
     * @param array $order
     * @param array $commodities
     * @param array $itemMasters
     * @return array
     */
    public function getValue($type, $order, $commodities = [], $itemMasters = [])
    {
        switch ($type) {
            case 'F_F':
                return (new F_F($order, $commodities, $itemMasters))->getData();
            case 'F_J':
                return (new F_J($order, $commodities))->getData();
            case 'F_JS':
                return (new F_JS($order, $commodities))->getData();
            case 'F_P':
                return (new F_P($order))->getData();
            case 'H_H':
                return (new H_H($order))->getData();
            case 'H_S':
                return (new H_S($order))->getData();
            case 'M_U':
                return (new M_U($order, $commodities, $itemMasters))->getData();
        }

        return [];
    }
}