<?php

namespace App\Services;

use App\Helpers\StringHelper;
use App\Models\Order;
use App\Repositories\CommodityRepository;
use App\Repositories\DeliveryRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Services\MakeshopApi\OrderQueryBuilder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService extends BaseService
{

    /** @var OrderQueryBuilder */
    private $orderQueryBuilder;
    private $orderRepository;
    private $commodityRepository;
    private $deliveryRepository;
    private $productRepository;

    /**
     * OrderService constructor.
     *
     * @param OrderQueryBuilder $OrderQueryBuilder
     * @param OrderQueryBuilder $orderQueryBuilder
     * @param OrderRepository $orderRepository
     * @param CommodityRepository $commodityRepository
     * @param DeliveryRepository $deliveryRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(
        OrderQueryBuilder $orderQueryBuilder,
        OrderRepository $orderRepository,
        CommodityRepository $commodityRepository,
        ProductRepository $productRepository,
        DeliveryRepository $deliveryRepository
    ) {
        $this->orderQueryBuilder   = $orderQueryBuilder;
        $this->orderRepository     = $orderRepository;
        $this->commodityRepository = $commodityRepository;
        $this->deliveryRepository  = $deliveryRepository;
        $this->productRepository  = $productRepository;
    }

    /**
     * Get order info by ordernum
     *
     * @param mixed $orderNumber
     * @return Collection
     */
    public function findByOrderNumber($orderNumber)
    {
        $orders = $this->orderQueryBuilder->get([
            'ordernum' => $orderNumber,
            'canceled' => 1,
        ]);

        return collect($orders)->first();
    }

    /**
     * Get order info by time range {from} - {to}
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @param \DateTime $startTime
     * @param \DateTime|null $endTime
     * @return void
     */
    public function findByTimeRange(\DateTime $startTime, \DateTime $endTime = null)
    {
        $timeFormat = 'YmdHis';
        if (empty($endTime)) {
            $endTime = new \DateTime();
        }
        $params = [
            'start' => $startTime->format($timeFormat),
            'end'   => $endTime->format($timeFormat),
        ];
        $orders = $this->orderQueryBuilder->get($params);

        return $orders;
    }

    /**
     * Save order from EC to DB by time range from - to
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @param \DateTime $startTime
     * @param \DateTime $endTime
     * @return void
     */
    public function saveOrderByTimeRange(\DateTime $startTime, \DateTime $endTime, $skipNew = true)
    {
        Log::info("START_END: {$startTime->format('YmdHis')} - {$endTime->format('YmdHis')}");
        $orders = $this->findByTimeRange($startTime, $endTime);
        Log::info(count($orders) . '-' . config('makeshop.batch.order.limit_per_batch'));
        if (count($orders) >= config('makeshop.batch.order.limit_per_batch')) {
            Log::info('Split into 2 time run');
            $half     = (int) (($startTime->getTimestamp() + $endTime->getTimestamp()) / 2);
            $endTime1 = new \DateTime();
            $endTime1->setTimestamp($half);
            $result = true;
            Log::info('Start to update the first order');
            $result *= $this->saveOrderByTimeRange($startTime, $endTime1);
            Log::info('Start to update the second order');
            $result *= $this->saveOrderByTimeRange($endTime1, $endTime);

            return $result;
        } else {
            Log::info('Start to update order');
            foreach ($orders as $order) {
                if (!$this->saveOrderFromEC($order)) {

                    return false;
                }
            }

            return true;
        }
    }

    /**
     * Save order from EC to DB
     *
     * @param array $order
     * @param mixed $data
     * @return bool
     */
    public function saveOrderFromEC($data, $skipNew = true)
    {
        try {
            foreach ($data as $name => $value) {
                $data[$name] = empty($value) ? null : $value;
                if ($name == 'status') {
                    $data[$name] = (int) $data[$name];
                }
            }
            foreach ($data['buyer'] as $name => $value) {
                $data['buyer_' . $name] = empty($value) ? null : $value;
            }
            foreach ($data['orderdetail'] as $name => $value) {
                $data[$name] = empty($value) ? null : $value;
            }
            $order = $this->findDB($data['ordernum']);
            if ($order) {
                Log::info("Existed order: {$order->ordernum} - {$order->id} - {$order->postsuban}");
                $data['old_postsuban'] = $order->postsuban;
                $data['old_sumprice'] = $order->sumprice;
                $data['old_order_date'] = $order->date;
                if ($skipNew === true) {

                    return true;
                }
            } else {
                Log::info("Create new order: {$data['ordernum']}");
            }
            $order = $this->orderRepository->create($data);
            // Generate new postsuban
            $newPostsuban = $this->generatePostsubanNumber();
            $this->orderRepository->update(['postsuban' => $newPostsuban], $order->id);

            $commodities = data_get($data, 'orderdetail.commodities');
            $commodities = array_values($commodities);
            if (isset($commodities[0][0]['name'])) {
                $commodities = $commodities[0];
            }
            foreach ($commodities as $commodity) {
                foreach ($commodity as $name => $value) {
                    $commodity[$name] = empty($value) ? null : $value;
                }
                $commodity['ordernum'] = $data['ordernum'];
                $commodity['order_id'] = $order->id;
                $commodity['jancode'] = StringHelper::removeWhiteSpace($commodity['jancode']);
                if (data_get($commodity, 'orgoptioncode')) {
                    $productDB = $this->productRepository->all(['ubrand_code' => data_get($commodity, 'orgoptioncode')])
                        ->whereNotNull('parent_code')
                        ->first();
                    if ($productDB) {
                        $commodity['jancode'] = $productDB->jancode;
                    }
                }
                $this->commodityRepository->create($commodity);
            }
            $deliveries = data_get($data, 'deliveries');
            $deliveries = array_values($deliveries);
            foreach ($deliveries as $delivery) {
                foreach ($delivery as $name => $value) {
                    $delivery[$name] = empty($value) ? null : $value;
                    if ($name == 'scheduled_shipping_date' || $name == 'shipping_date') {
                        if ($value == config('makeshop.api.response.zero_date_format')) {
                            $delivery[$name] = null;
                        }
                    }
                }
                $delivery['ordernum'] = $data['ordernum'];
                $delivery['order_id'] = $order->id;
                $this->deliveryRepository->create($delivery);
            }

            return true;
        } catch (\Exception $ex) {
            Log::info('DB error: ' . $data['ordernum'] . ' ' . $ex->getMessage() . ' ' . $ex->getLine()  . ' ' . $ex->getFile());

            return false;
        }
    }

    /**
     * Get all orders that shipping is null
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getMissedShippingOrders()
    {
        return $this->orderRepository->makeModel()->newQuery()
            ->whereHas('deliveries', function ($query) {
                $query->whereNull('shipping_date');
            })
            ->orderBy('ordernum', 'DESC')
            ->orderBy('id', 'DESC');
    }

    /**
     * Generate Postsuban number
     *
     * 既にテーブルに入っている最後のレコードのpostsubanが1であれば次のレコードには2を設定。
     * 既にテーブルに入っている最後のレコードのpostsubanが9999であれば次のレコードには1を設定するような仕様です。
     * 値が重複しますが、注文日とpostsubanの複合キーになるため、一日の受注が１万件を超えない限りキーとして成立します。
     * postsubanは重複する値が入るため、AUTO_INCREMENTなどを利用すると実現できないためご注意ください。
     *
     * @param Order $order
     * @return integer
     */
    public function generatePostsubanNumber()
    {
        $maxPostsuban = (int) config('makeshop.max_potsuban');
        $lastOrder = $this->getLastOrdersByPostsuban()->first();
        $lastPos = $lastOrder->postsuban ?? 0;
        $postsuban = $lastPos >= $maxPostsuban ? 1 : $lastPos + 1;

        return $postsuban;
    }

    /**
     * Get last orders by Postsuban
     *
     * @return void
     */
    public function getLastOrdersByPostsuban()
    {
        return $this->orderRepository->makeModel()
            ->newQuery()
            ->whereNotNull('postsuban')
            ->orderBy('id', 'DESC');
    }

    /**
     * Find order in DB by ordernum
     *
     * @param string $ordernum
     * @return Order
     */
    public function findDB($ordernum)
    {
        return $this->orderRepository->makeModel()
            ->where('ordernum', $ordernum)
            ->orderBy('date', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * Update shipping data - shipping date
     *
     * @param array $data
     * @param Order $order
     * @return void
     */
    public function updateShippingData($data, Order $order)
    {
        try {
            DB::beginTransaction();
            $deliveries = array_values($data);
            foreach ($deliveries as $delivery) {
                if (!empty($delivery['shipping_date'])) {
                    $this->deliveryRepository->makeModel()
                        ->where('ordernum', $order->ordernum)
                        ->where('order_id', $order->id)
                        ->where('delivery_id', $delivery['delivery_id'])
                        ->update(['shipping_date' => $delivery['shipping_date']]);

                    Log::info("Update shipping date: {$order->ordernum}-{$order->postsuban} -> {$delivery['shipping_date']}");
                } else {
                    Log::info("Shipping date still empty: {$order->ordernum}-{$order->postsuban}");
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('[ROLLBACK] error: ' . $ex->getMessage() . ' ' . $ex->getLine() . ' ' . $ex->getFile());
        }
    }

    /**
     * Get order by shipping date
     *
     * @return array
     */
    public function getOrderByShippingDate()
    {
        // 必要なデータを一括で取得しているため、 out of memory が発生した場合 cursor()や chunk()の利用を検討する
        $shippingDaysAgo = config('mix.sftp.uriage_csv.shipping_days_ago');
        $start = Carbon::now()->subDays($shippingDaysAgo)->startOfDay();
        $end   = Carbon::now()->subDays($shippingDaysAgo)->endOfDay();
        Log::info('Get orders from ' . $start->format('Y-m-d H:i:s') . ' -> ' . $end->format('Y-m-d H:i:s'));
        $orders = $this->orderRepository->findByShippingDate($start, $end);

        return $orders->map(function ($order) {
            return $order->maxOrdernum;
        });
    }

}
