<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Console\Command;
use \App\Traits\CommandLogTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderEC2DB extends Command
{
    use CommandLogTrait;
    private $orderService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:ec2db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ec受注取得バッチ';

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
     * @return mixed
     */
    public function handle()
    {
        $path   = storage_path('logs/batch_order_ec2db.log');
        config(['logging.channels.daily.path' => $path]);
        ini_set('memory_limit', '512M');

        // Check last succeed time
        if (!$this->ableToExecute()) {
            Log::info('[' . $this->getCommandName() . ']' . ' Skip this run time at ' . date('Y-m-d H:i:s'));

            return;
        }
        try {
            // Write start log and save start time
            $this->startLog();
            $startTime = $this->getLastSucceedTime() ?? null;
            if (empty($startTime)) {
                $startTime = new \DateTime();
                $startTime->setTimestamp(0);
            }
            $startTime = $this->downTimeByMinute($startTime);
            $endTime   = $this->downTimeByMinute(new \DateTime());
            // Save order by time range from-to
            DB::beginTransaction();
            $result = $this->orderService->saveOrderByTimeRange($startTime, $endTime);
            if ($result) {
                DB::commit();
                // Write success log and save last success time
                $this->logSuccess($endTime);
            } else {
                DB::rollBack();
                Log::info('DB rollback');
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::info('DB rollback');
            // Error when process
            Log::error($ex->getMessage());
        } finally {
            // Write end log and save
            $this->endLog();
        }
    }

    /**
     * Downtime by minute
     *
     * @param \DateTime $dateTime
     * @param integer $minute
     * @return \DateTime
     */
    private function downTimeByMinute($dateTime)
    {
        $period = (int) config('makeshop.batch.downtime_period');
        $hour   = (int) $dateTime->format('H');
        $minute = (int) $dateTime->format('i');
        $small  = $minute % $period;
        $minute = $minute - $small;
        $dateTime->setTime($hour, $minute, 0);

        return $dateTime;
    }
}
