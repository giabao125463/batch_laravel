<?php

namespace App\Console\Commands;

use App\Helpers\StringHelper;
use App\Models\MixOrder;
use App\Services\OrderService;
use App\Services\MixOrderService;
use App\Traits\BackupFileTrait;
use App\Traits\CsvTrait;
use Illuminate\Console\Command;
use App\Traits\CommandLogTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrderDb2Mix extends Command
{
    use CsvTrait, CommandLogTrait, BackupFileTrait;
    private $orderService;
    private $mixOrderService;
    private $errors;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:db2mix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ec2mix売上連携';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(OrderService $orderService, MixOrderService $mixOrderService)
    {
        parent::__construct();
        $this->orderService = $orderService;
        $this->mixOrderService = $mixOrderService;
        $this->errors = collect([]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setBackupDirPath(config('console.backup.batchs.orderdb2mix'));
        $path   = storage_path('logs/batch_order_db2mix.log');
        config(['logging.channels.daily.path' => $path]);
        config(['excel.exports.csv.enclosure' => '']);
        try {
            // Write start log and save start time
            $this->startLog();
            DB::beginTransaction();
            // Get data from db
            list($data, $header) = $this->getData();
            if ($this->errors->count() > 0) {
                foreach($this->errors as $error) {
                    Log::error($error);
                }
                throw new \Exception('MixOrdernum processing was failed');
            }

            // Create file csv
            $date = Carbon::now();
            $namePrefix = config('mix.sftp.uriage_csv.name_prefix');
            $name = "{$namePrefix}{$date->format("YmdHis")}.csv";
            if (!empty($data)) {
                $localPathFileCsv = Storage::path($name);
                $this->exportCsvToFile($localPathFileCsv, $data);
            }
            $csvHeader = implode(',', $header);
            Storage::prepend($name, $csvHeader);

            // Put csv file to sftp-mix
            $csvConfig = config('mix.sftp.paths.uriage');
            // /EC2MIX/uriage/$name
            $mixPath = $csvConfig . $name;
            $result = $this->putCsvToMix($mixPath, $name);
            if ($result) {
                DB::commit();
                Log::info('Uploading ' . $name . ' successful');
                // Write success log and save last success time
                $this->logSuccess();
            } else {
                throw new \Exception('Uploading ' . $name . ' fail');
            }
        } catch (\Exception $ex) {
            // Error when process
            Log::error($ex->getMessage());
            DB::rollBack();
        } finally {
            // Write end log and save
            $this->endLog();
        }
    }

    /**
     * Get data from db
     *
     * @return array
     */
    public function getData()
    {
        $csvItemMaxLength = (int) config('excel.exports.csv.item.max_length');
        $kanaReplace = config('mix.csv.kanareplace');
        $now = Carbon::now();
        $data = [];
        $header = [];
        $header['class'] = config('mix.sftp.uriage_csv.class');
        $header['date'] = $now->format('Ymd');
        $header['total'] = 0;

        // Get all orders from days ago
        $orders = $this->orderService->getOrderByShippingDate();
        Log::info('Order count: ' . count($orders));
        $index = 0;
        foreach ($orders as $order) {
            if ($this->hasOrderError($order)) {
                continue;
            }

            $link = $order->link;
            $mixOrder = $this->mixOrderService->create($order);
            if (empty($mixOrder)) {
                $this->errors->push('MixOrder mapping fail with ordernum=' . $order->ordernum . ' date=' . $order->date);

                return [null, null];
            }
            $commodities = $order->commodities;
            foreach ($commodities as $commoditie) {
                $kanaName = preg_match('/^[ァ-ヶ]*$/u', $order['buyer_kana']) ? $order['buyer_kana'] : $kanaReplace;
                // Prevent error maximum array's integer index
                // Array key should be a string
                $key = $mixOrder->mixnum . '_jc_' . $commoditie['jancode'];
                if (!isset($data[$key])) {
                    $commoditieName = $this->removeSpecialContent($commoditie['name']);
                    $data[$key] = [
                        'index'             => ++$index,
                        'member_id'         => $link->team26_id ?? '',
                        'member_name_kana'  => $kanaName,
                        'ordernum'          => $mixOrder->mixnum,
                        'date'              => $order->date->format('Ymd'),
                        'jancode'           => $commoditie['jancode'],
                        'name'              => StringHelper::subString($commoditieName, $csvItemMaxLength),
                        'amount'            => 0,
                        'price'             => $commoditie['price']
                    ];
                }
                $data[$key]['amount'] += $commoditie['amount'];
            }
        }
        $header['total'] = count($data);

        return [$data, $header];
    }

    /**
     * Put file from local to Mix storage
     *
     * @param $mixPath String
     * @param $localPath String
     *
     * @return bool
     */
    private function putCsvToMix($mixPath, $name)
    {
        if (Storage::exists($name)) {
            $content = Storage::get($name);
            // Backup storage local MAO[YmdHis].csv -> storage backup MAO[YmdHis].csv
            $this->backup(Storage::path($name), $name);
            Storage::disk('sftp-mix')->put($mixPath, $content);
            return true;
        }
        return false;
    }

    /**
     * Check parameters in order data
     *
     * @param $order
     *
     * @return bool
     */
    private function hasOrderError($order)
    {
        $link = $order->link;
        if (empty($link)) {
            Log::error('Links data not found. ordernum=' . $order->ordernum . ' buyer_id=' . $order->buyer_id);
            return true;
        }
        if (empty($link->team26_id)) {
            Log::error('Links data found. But "team26_id" is nothing. ordernum=' . $order->ordernum . ' buyer_id=' . $order->buyer_id);
            return true;
        }
        return false;
    }
}