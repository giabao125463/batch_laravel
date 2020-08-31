<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\Traits\CommandLogTrait;
use \App\Traits\CsvTrait;
use \App\Traits\FtpTrait;
use \App\Services\ProductService;
use App\Traits\BackupFileTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Pos2EcStock extends Command
{
    use CommandLogTrait, FtpTrait, CsvTrait, BackupFileTrait;

    /** @var ProductService */
    private $productService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:posdb2ec';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'pos2ec在庫連携';

    /**
     * Create a new command instance.
     *
     * @param ProductService $productService
     * @param ProductRepository $productRepository
     * @return void
     */
    public function __construct(ProductService $productService)
    {
        parent::__construct();
        $this->productService = $productService;
        $this->logName        = $this->signature;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setBackupDirPath(config('console.backup.batchs.pos2ecstock'));
        $path = storage_path('logs/batch_stock_posdb2ec.log');
        config(['logging.channels.daily.path' => $path]);
        ini_set('memory_limit', '512M');
        if (!$this->ableToExecute()) {
            Log::info('[' . $this->getCommandName() . ']' . ' Skip this run time at ' . date('Y-m-d H:i:s'));

            return;
        }

        $this->startLog();
        $posConfig      = config('pos');
        $makeshopConfig = config('makeshop');

        $remoteDir      = data_get($posConfig, 'ftp.paths.m_zaiko'); // /outputDataEC/
        $posFileName    = data_get($posConfig, 'ftp.file_names.m_zaiko'); // m_zaiko.txt
        $csvFileName    = data_get($posConfig, 'local.file_names.m_zaiko'); //m_zaiko.tsv
        $uploadFileName = data_get($makeshopConfig, 'api.product.file_names.update'); // updateProduct.csv
        $tsvHeaders     = data_get($posConfig, 'tsv_headers.m_zaiko');
        if ($this->copyFileFromFtpToLocal($posFileName, $remoteDir)) {
            // Backup file m_zaiko.txt -> m_zaiko_{YmdHis}.txt
            $backupName = str_replace('.txt', '_', $posFileName);
            $backupName .= Carbon::now()->format('YmdHi') . '.txt';
            $this->backup(Storage::path($posFileName), $backupName);
            // Load POS data from tsv
            $header = implode("\t", $tsvHeaders);
            $this->appendHeaderForCsv($csvFileName, $header, Storage::get($posFileName));
            $csvData = $this->csvFileToArray(Storage::path($csvFileName));

            // Update inventory quantity and product display
            $scanCodeIndex = data_get($posConfig, 'csv_indexs.m_zaiko.scancode_new'); // scancode_new => 3
            $zaikoNumIndex = data_get($posConfig, 'csv_indexs.m_zaiko.zaiko_num'); // zaiko_num => 6
            foreach ($csvData as $posData) {
                $scanCode  = $posData[$scanCodeIndex];
                $posStock  = $posData[$zaikoNumIndex];
                // Get product stock from Database
                $productDB = $this->productService->findByJancode($scanCode);
                if (!isset($productDB)) {
                    Log::error('[' . $this->getCommandName() . ']' . ' Product not found in database: jancode=' . $scanCode);
                    continue;
                }
                if ($productDB->stock === null) {
                    Log::info('[DB] Product stock is unlimited. jancode=' . $scanCode);
                    continue;
                }
                $ubrandCode = $productDB->isParent ? $productDB->ubrand_code : $productDB->parent_code;
                // Get EC stock from API
                $productEC = $this->getProduct($ubrandCode);

                // Skip when product stock is unlimited
                $stockCheck = data_get($productEC, 'stock');
                if (!$productDB->isParent) {
                    foreach (data_get($productEC, 'option.select_options') as $option) {
                        if (data_get($option, 'jancode') == $scanCode) {
                            $stockCheck = data_get($option, 'stock');
                            break;
                        }
                    }
                }
                if ($stockCheck === null) {
                    Log::info('[API] Product stock is unlimited. jancode=' . $scanCode);
                    continue;
                }

                if (!isset($productEC)) {
                    Log::error('[' . $this->getCommandName() . ']' . ' Product not found on API: ubrand_code=' . $ubrandCode);
                    continue;
                }
                // Check publish date to value
                if (!empty($productEC['publish_date_to'])) {
                    $publishTo = \DateTime::createFromFormat('YmdH', $productEC['publish_date_to']);
                    $publishTo->setTime(0, 0, 0);
                    $now = new \DateTime();
                    $now->setTime(0, 0, 0);
                    if ($publishTo < $now) {
                        Log::info('"Publish date to" is past. Skip this updating for ubrand_code=' . $ubrandCode . ' publish_to=' . $publishTo->format('Y-m-d'));
                        continue;
                    }
                }
                // Hide product while processing
                $productDisplay = $productEC['is_display'];
                if ($productDisplay === 'Y') {
                    $execSuccess = $this->updateProduct($uploadFileName, [
                        'ubrand_code' => $ubrandCode,
                        'is_display'  => 'N',
                    ]);
                    if (!$execSuccess) {
                        Log::error('[' . $this->getCommandName() . ']' . ' Failed to update product: ubrand_code=' . $ubrandCode . ', is_display=N');
                        continue;
                    }
                } else {
                    Log::info('Product display is "N". Skip this updating for ubrand_code=' . $ubrandCode);
                    continue;
                }
                $isDisplay   = 'Y';
                // Get EC stock from API
                $productEC  = $this->getProduct($ubrandCode);
                $newECStock = (int) $productEC['stock'];
                if (!$productDB->isParent) {
                    foreach(data_get($productEC, 'option.select_options') as $option) {
                        if (data_get($option, 'jancode') == $scanCode) {
                            $newECStock = (int) data_get($option, 'stock');
                            break;
                        }
                    }
                }
                // Get product stock from Database
                $productDB = $this->productService->findByJancode($scanCode);
                if (!$productDB) {
                    Log::error('[' . $this->getCommandName() . ']' . ' Product not found in database: jancode=' . $scanCode);
                    $this->restoreProductDisplay($ubrandCode, 'Y', $uploadFileName);
                    continue;
                }
                $updateStock = $posStock - ($newECStock - (int)$productDB->stock);
                Log::debug('newStock = zaikoStock - (ecStock - dbStock)');
                Log::debug("{$posStock} - ({$newECStock} - {$productDB->stock})");
                $updateStock = $updateStock < 0 ? 0 : $updateStock;
                // Create CSV to update new stock and product display
                $execSuccess = false;
                if (empty($productDB->parent_code)) {
                    $execSuccess = $this->updateProduct($uploadFileName, [
                        'ubrand_code' => $ubrandCode,
                        'new_stock'   => $updateStock,
                        'is_display'  => $isDisplay
                    ]);
                } else {
                    $execSuccess = $this->updateProductOption($uploadFileName, [
                        'parent_ubrand_code' => $productDB->parent_code,
                        'ubrand_code' => $productDB->ubrand_code,
                        'new_stock'   => $updateStock,
                    ]);
                    $this->restoreProductDisplay($productDB->parent_code, 'Y', $uploadFileName);
                }
                if (!$execSuccess) {
                    Log::error('[' . $this->getCommandName() . ']' . ' Failed to update product: ubrand_code=' . $ubrandCode . ', is_display=' . $isDisplay);
                } else {
                    Log::info("Update stock success: jancode=$scanCode ubrand_code=$ubrandCode newStock:$updateStock");
                }
            }
            $this->logSuccess();
        } else {
            Log::error('File not found on FTP server: ' . $posFileName);
        }
        $this->endLog();
    }

    /**
     * Get Product information from API
     *
     * @param $scancode String
     *
     * @return array
     */
    private function getProduct($scancode)
    {
        $response = $this->productService->search(['ubrand_code' => $scancode]);
        if (data_get($response, 'status_code') != 200) {
            return null;
        }

        return data_get($response, 'product_list')[0];
    }

    /**
     * Call API to update product information
     *
     * @param $fileName String
     * @param $data array
     *
     * @return bool
     */
    private function updateProduct($fileName, $data)
    {
        $this->createUpdateProductCSV($fileName, $data);

        return $this->productService->updateProduct(Storage::path($fileName));
    }

    /**
     * Call API to update product option information
     *
     * @param $fileName String
     * @param $data array
     *
     * @return bool
     */
    private function updateProductOption($fileName, $data)
    {
        $this->createUpdateProductOptionCSV($fileName, $data);

        return $this->productService->updateProduct(Storage::path($fileName), true);
    }

    /**
     * Create csv for update product info by API
     *
     * @param $fileName String
     * @param $data array
     *
     * @return void
     */
    private function createUpdateProductCSV($fileName, $data = [])
    {
        $csvConfig      = config('csv.update_products');
        $updateData     = array_fill(1, $csvConfig['columns'], '');
        $updateData[1]  = '1';
        $updateData[4]  = $data['ubrand_code'];
        $updateData[16] = isset($data['new_stock']) ? $data['new_stock']  : '';
        $updateData[47] = $data['is_display'];
        Log::info('API Update product: ' . json_encode($data));

        // Write content to file
        if (Storage::exists($fileName)) {
            Storage::delete($fileName);
        }
        Storage::put($fileName, $csvConfig['header']);
        Storage::append($fileName, implode($updateData, $csvConfig['delimiter']));
    }

    /**
     * Create csv for update product option info by API
     *
     * @param $fileName String
     * @param $data array
     *
     * @return void
     */
    private function createUpdateProductOptionCSV($fileName, $data = [])
    {
        $csvConfig      = config('csv.update_product_options');
        $updateData     = array_fill(1, $csvConfig['columns'], '');
        $updateData[1]  = '1';
        $updateData[2]  = '0';
        $updateData[4]  = $data['parent_ubrand_code'];
        $updateData[7]  = $data['ubrand_code'];
        $updateData[11] = isset($data['new_stock']) ? $data['new_stock']  : '';
        Log::info('API Update product option: ' . json_encode($data));

        // Write content to file
        if (Storage::exists($fileName)) {
            Storage::delete($fileName);
        }
        Storage::put($fileName, $csvConfig['header']);
        Storage::append($fileName, implode($updateData, $csvConfig['delimiter']));
    }

    /**
     * Restore product display
     *
     * @param $scanCode string
     * @param $productDisplay string
     * @param $uploadFileName string
     *
     * @return void
     */
    private function restoreProductDisplay($scanCode, $productDisplay, $uploadFileName)
    {
        if ($productDisplay === 'Y') {
            $execSuccess = $this->updateProduct($uploadFileName, [
                'ubrand_code' => $scanCode,
                'is_display'  => $productDisplay,
            ]);
            if (!$execSuccess) {
                Log::error('[' . $this->getCommandName() . ']' . ' Failed to update product: ubrand_code=' . $scanCode . ', is_display=Y');
            }
        }
    }
}
