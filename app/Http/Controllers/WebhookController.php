<?php

namespace App\Http\Controllers;

use App\Console\Commands\OrderDB2POS;
use App\Services\OrderService;
use App\Services\CommandLogService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends BaseController
{
    private $orderService;
    private $commandLogService;

    /**
     * Constructor
     *
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService, CommandLogService $commandLogService)
    {
        $this->orderService = $orderService;
        $this->commandLogService = $commandLogService;
    }

    /**
     * Webhook for order status
     *
     * @param Request $request
     * @return void
     */
    public function orderStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            // handle request
            $this->handle($request);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('There is error while processing webhook request.');
            Log::error('This webhook need to be called again.');

            $orderNum = $request->request->get('order');
            $cmd = $request->request->get('cmd');

            Log::error('▼▼▼▼▼▼▼▼WEBHOOK-ERROR▼▼▼▼▼▼▼▼');
            Log::error('Ordernum: ' . $orderNum);
            Log::error('CMD: ' . $cmd);
            Log::error('Hooked at: {'.date('Y-m-d H:i:s').'}');
            Log::error('▲▲▲▲▲▲▲▲WEBHOOK-ERROR▲▲▲▲▲▲▲▲');
        }
    }

    /**
     * Handle Webhook
     *
     * @param Request $request
     */
    public function handle(Request $request)
    {
        Log::info('--------START-WEBHOOK--------');
        $orderNum = $request->request->get('order');
        $cmd = $request->request->get('cmd');
        if (empty($orderNum)) {
            Log::error('[INVALID_HOOK] Ordernum is empty.');
            abort(404);
        }
        switch($cmd) {
            case 0: //受注時- - Order create
                // Batch order:ec2db
                // No need to action here
                Log::info('Webook <ORDER CREATED>: ' . $orderNum);
                break;
            case 1: //注文内容修正時 - Order edit
            case 2: //注文キャンセル時 - Order cancel
                $action = ($cmd == 1 ? 'EDITED' : 'CANCELED');
                Log::info('Webhook <'. $action .' ORDER>: ' . $orderNum);
                $order = $this->orderService->findDb($orderNum);
                if ($order) {
                    $updateDate = null;
                    $commandLog = $this->commandLogService->find(OrderDB2POS::class);
                    if (!empty($commandLog)) {
                        $updateDate = $commandLog->last_succeed_time;
                        $updateDate->modify('+1 second');
                    }

                    // Dulicate existed order with status=0 and old_sumprice
                    $newOrder = $order->replicate();
                    $newOrder->status = 0;
                    $newOrder->postsuban = $this->orderService->generatePostsubanNumber();
                    $newOrder->old_sumprice = $order->sumprice;
                    $newOrder->date_update = $updateDate;
                    $newOrder->save();
                    foreach ($order->commodities as $comm) {
                        $newComm = $comm->replicate();
                        $newComm->order_id = $newOrder->id;
                        $newComm->save();
                    }
                    foreach ($order->deliveries as $deliv) {
                        $newdeliv = $deliv->replicate();
                        $newdeliv->order_id = $newOrder->id;
                        $newdeliv->save();
                    }

                    //注文内容修正時 - Order edit
                    if ($cmd == 1) {
                        $data  = $this->orderService->findByOrderNumber($orderNum);
                        $data['date_update'] = $updateDate;
                        $data['status'] = '1';

                        $this->orderService->saveOrderFromEC($data, false);
                    }
                } else {
                    Log::info("[ORDER_{$action}] Ordernum does not exist in DB. Ordernum={$orderNum}");
                }
                break;
            case 3: //入金完了時 - Payment complete
                Log::info('Webook <PAYMENT COMPLETED>: ' . $orderNum);
                // Have no setting for this hook
                break;
            case 4: //配送完了時 - Delivery complete
                Log::info('Webook <DELIERY COMPLETED>: ' . $orderNum);
                // Have no setting for this hook
                break;
            default:
                Log::error('[INVALID_HOOK] CMD is not valid CMD=' . $cmd);
                abort(404);
                break;
        }
        Log::info('--------END-WEBHOOK--------');
    }
}
