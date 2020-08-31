<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Repositories\MixOrderRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MixOrderService extends BaseService
{

    private $orderRepository;
    private $mixOrderRepository;

    /**
     * MixOrderService constructor.
     *
     * @param OrderRepository $orderRepository
     * @param MixOrderRepository $mixOrderRepository
     */
    public function __construct(
        OrderRepository $orderRepository,
        MixOrderRepository $mixOrderRepository
    ) {
        $this->orderRepository    = $orderRepository;
        $this->mixOrderRepository = $mixOrderRepository;
    }

    /**
     * Create new mixOrder
     *
     * @param Order $order
     * @param Carbon $date
     * @return MixOrder
     */
    public function create(Order $order)
    {
        try {
            $mixnumConf = config('mix.order.mixnum');
            $data       = [
                'ordernum'     => $order->ordernum,
                'sales_date'   => $order->date,
            ];
            $mixOrder   = $this->mixOrderRepository->create($data);
            $prefix     = (int) data_get($mixnumConf, 'prefix');
            $length     = (int) data_get($mixnumConf, 'length');
            $mixUnit    = pow(10, $length);
            $mixNumBase = $prefix * $mixUnit;
            $mixIndex   = $mixOrder->id % $mixUnit;
            $mixnum     = $mixNumBase + $mixIndex;
            // 現状のパフォーマンスとしては create() → update()として問題ないが、もし問題があった場合は max(id) +1 → create()を検討する
            $mixOrder   = $this->mixOrderRepository->update(['mixnum' => $mixnum], $mixOrder->id);

            return $mixOrder;
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . ' ' . $ex->getLine() . ' ' . $ex->getFile());

            return null;
        }

    }

}
