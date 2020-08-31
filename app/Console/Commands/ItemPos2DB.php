<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Traits\CsvTrait;
use App\Traits\FtpTrait;
use App\Repositories\ItemMasterRepository;
use App\Traits\CommandLogTrait;
use App\Models\CommandLog;
use App\Traits\BackupFileTrait;
use App\Traits\Csv\POS\MpluImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ItemPos2DB extends Command
{
    use CsvTrait, FtpTrait, CommandLogTrait, BackupFileTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'item:pos2db';

    protected $itemMasterRepository;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'pos2ec商品マスタ連携';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ItemMasterRepository $itemMasterRepository)
    {
        parent::__construct();
        $this->itemMasterRepository = $itemMasterRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setBackupDirPath(config('console.backup.batchs.itempos2db'));
        $path   = storage_path('logs/batch_item_pos2db.log');
        config(['logging.channels.daily.path' => $path]);
        // Check last succeed time
        if (!$this->ableToExecute()) {
            Log::info('[' . $this->getCommandName() . ']' . ' Skip this run time at ' . date('Y-m-d H:i:s'));
            return;
        }
        $this->startLog();
        $posConfig = config('pos');
        $posFileName = data_get($posConfig, 'ftp.file_names.m_plu');
        $remoteDir = data_get($posConfig, 'ftp.paths.m_plu');
        $tsvFileName = data_get($posConfig, 'local.file_names.m_plu');

        if ($this->copyFileFromFtpToLocal($posFileName, $remoteDir)) {
            // Clean old file
            if (Storage::exists($tsvFileName)) {
                Storage::delete($tsvFileName);
            }
            // Backup m_plu.txt -> m_plu_{YmdHis}.txt
            $backupName = str_replace('.txt', '_', $posFileName);
            $backupName .= Carbon::now()->format('YmdHi') . '.txt';
            $this->backup(Storage::path($posFileName), $backupName);
            // Rename file to .tsv
            Storage::move($posFileName, $tsvFileName);
            $tsvHeaders = data_get($posConfig, 'tsv_headers.m_plu');
            $header = implode("\t", $tsvHeaders);
            // Append header to keep empty values
            $this->appendHeaderLine($header, storage_path('app/' . $tsvFileName));
            Log::info('Import file: ' . $tsvFileName);
            Excel::import(new MpluImport, $tsvFileName);
            $this->logSuccess();
        } else {
            Log::error('File not found on FTP server: ' . $posFileName);
        }
        $this->endLog();
    }
}
