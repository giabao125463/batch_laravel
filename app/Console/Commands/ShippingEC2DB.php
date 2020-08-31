<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Traits\CommandLogTrait;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;

class ShippingEC2DB extends Command
{
    use CommandLogTrait;

    private $orderService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipping:ec2db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '出荷情報更新';

    /**
     * Create a new command instance.
     *
     * @param OrderService $orderService
     * @return void
     */
    public function __construct(OrderService $orderService)
    {
        parent::__construct();
        $this->orderService = $orderService;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $path   = storage_path('logs/batch_shipping_ec2db.log');
        config(['logging.channels.daily.path' => $path]);
        if (!$this->ableToExecute()) {
            Log::info('[' . $this->getCommandName() . ']' . ' Skip this run time at ' . date('Y-m-d H:i:s'));

            return;
        }
        try {
            $this->startLog();

            $normalKeys = [];
            $cancelKeys = [];
            $orders = $this->orderService->getMissedShippingOrders();
            foreach($orders->cursor() as $order) {
                if (!in_array($order->ordernum, $normalKeys) && !in_array($order->ordernum, $cancelKeys)) {
                    if (empty($order->status)) {
                        $cancelKeys[] = $order->ordernum;
                    } else {
                        $normalKeys[] = $order->ordernum;

                        try {
                            Log::info('Start updating delivery Ordernum=' . $order->ordernum);
                            $data = $this->orderService->findByOrderNumber($order->ordernum);
                            $this->orderService->updateShippingData(data_get($data, 'deliveries'), $order);
                        } catch (\Exception $ex) {
                            Log::error('Update delivery Ordernum=' . $order->ordernum);
                            Log::error('Error: ' . $ex->getMessage() . ' ' . $ex->getLine() . ' ' . $ex->getFile());
                        } finally {
                            Log::info('End updating');
                        }
                    }
                }
            }
            $this->logSuccess();
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
        } finally {
            $this->endLog();
        }
    }
}
