<?php

namespace App\Console\Commands;

use App\Models\CommandLog;
use App\Models\Order;
use App\Services\PosXmlService;
use App\Repositories\OrderRepository;
use App\Traits\BackupFileTrait;
use Illuminate\Console\Command;
use \App\Traits\CommandLogTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrderDB2POS extends Command
{
    use CommandLogTrait, BackupFileTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:db2pos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '連携バッチ開発(php) ec2pos売上連携';

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var PosXmlService
     */
    protected $posXmlService;

    /**
     * Create a new command instance.
     *
     * @param $orderRepository
     * @param $posXmlService
     * @return void
     */
    public function __construct(OrderRepository $orderRepository, PosXmlService $posXmlService)
    {
        parent::__construct();

        $this->logName = 'order_ec_2_pos';
        $this->orderRepository = $orderRepository;
        $this->posXmlService = $posXmlService;
    }

    /**
     * Execute the console command.
     *
     * $param
     * @return mixed
     */
    public function handle()
    {
        $this->setBackupDirPath(config('console.backup.batchs.orderdb2pos'));
        $path   = storage_path('logs/batch_order_db2pos.log');
        config(['logging.channels.daily.path' => $path]);
        if (!$this->ableToExecute()) {
            Log::info('[' . $this->getCommandName() . ']' . ' Skip this run time at ' . date('Y-m-d H:i:s'));
            return;
        }

        // check Error File
        if ($this->existErrorFile()) {
            Log::error('Error TorihikiLog File Exists');
            return;
        }

        $this->startLog();
        try {
            $posConfig = config('pos');
            $path = data_get($posConfig, 'ftp.paths.torihikilog');
            $fileName = data_get($posConfig, 'ftp.file_names.torihikilog');
            $timeRange = $this->getValidTimeRange();
            // Check "FILE_LOCK_FIT" file existed in FTP server
            $lockFitFile = data_get($posConfig, 'ftp.lock_files.fit');
            $lockAPFile = data_get($posConfig, 'ftp.lock_files.ap');
            // Clean TorihikiLog file
            if (Storage::disk('local')->exists($fileName)) {
                Storage::disk('local')->delete($fileName);
            }
            $writeStream = fopen(storage_path('app/' . $fileName), 'w+');

            // if (Storage::disk('ftp-pos')->exists($path . $lockFitFile) == false) {
                // Set 'FILE_LOCK_AP' file in FTP server
                Log::info("Create FILE_LOCK_AP file");
                // Storage::disk('ftp-pos')->put($path . $lockAPFile, 'LOCK');

                // Get order records from database
                Log::info('Get orders: ' . $timeRange['start'] . ' - ' . $timeRange['end']);
                $builder = $this->orderRepository->getItemsByDates($timeRange['start'], $timeRange['end']);

                $chunkSize = config('pos.export.chunk_size');
                Log::info('Start chunk with size = ' . $chunkSize);
                $orderCount = 0;
                $result = true;
                $builder->chunk($chunkSize, function ($orders) use ($fileName, &$result, &$orderCount, $writeStream) {
                    Log::info('    Chunk index=' . $orderCount);
                    $orderCount += $orders->count();
                    $result = $this->posXmlService->outputXml($orders, $fileName, $writeStream);
                    if (!$result) {
                        Log::error('    Break chunk');
                    }
                    return $result;
                });
                Log::info('End chunk');
                fclose($writeStream);
                if (empty($timeRange['end'])) {
                    $timeRange['end'] =  Carbon::now();
                }
                // if ($orderCount > 0) {
                //     if ($result) {
                //         // Backup file TorihikiLog.xml -> TorihikiLog_{YmdHis}.txt
                //         $backupName = str_replace('.xml', '_', $fileName);
                //         $backupName .= Carbon::now()->format('YmdHi') . '.xml';
                //         $this->backup(Storage::path($fileName), $backupName);
                //         Log::info('Put TorihikiLog.xml in FTP server');
                //         Storage::disk('ftp-pos')->put($path . $fileName, Storage::readStream($fileName));
                //         $this->logSuccess($timeRange['end']);
                //     } else {
                //         Log::info('TorihikiLog.xml export process has been cancelled.');
                //         if (Storage::disk('local')->exists($fileName)) {
                //             Storage::disk('local')->delete($fileName);
                //         }
                //     }
                // } else {
                //     $this->logSuccess($timeRange['end']);
                //     Log::info("No orders to push XML");
                // }
            // } else {
            //     Log::info("$lockFitFile exists");
            // }
        } catch (\Exception $ex) {
            if (is_resource($writeStream)) {
                fclose($writeStream);
            }
            Log::error($ex->getMessage() . ' ' . $ex->getLine() . ' ' . $ex->getFile());
        } finally {
            // Delete 'FILE_LOCK_AP' file in FTP server
            Log::info("Clean FILE_LOCK_AP file");
            Storage::disk('ftp-pos')->delete($path . $lockAPFile);

            $this->endLog();
        }
    }

    /**
     * Check whether has error TorihikiLog file exists in FTP server
     *
     * @return bool
     */
    protected function existErrorFile()
    {
        $posConfig = config('pos');
        $path = data_get($posConfig, 'ftp.paths.torihikilog');

        $files = Storage::disk('ftp-pos')->allFiles($path);
        foreach ($files as $file) {
            if (strpos($file, '.ERR') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get valid time range for this batch
     *
     * @return array
     */
    protected function getValidTimeRange()
    {
        $order2ECCmdLog = CommandLog::where('command_name', OrderEC2DB::class)->first();
        $ec2PosCmdLog = CommandLog::where('command_name', OrderDB2POS::class)->first();

        $start = !empty($ec2PosCmdLog) ? $ec2PosCmdLog['last_succeed_time'] : '';
        $end = !empty($order2ECCmdLog) ? $order2ECCmdLog['last_succeed_time'] : '';

        return [
            'start' => $start,
            'end'   => $end
        ];
    }
}