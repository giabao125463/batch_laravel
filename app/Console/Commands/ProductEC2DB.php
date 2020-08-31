<?php

namespace App\Console\Commands;

use App\Services\ProductService;
use Illuminate\Console\Command;
use \App\Traits\CommandLogTrait;
use Illuminate\Support\Facades\Log;

class ProductEC2DB extends Command
{
    use CommandLogTrait;
    private $productService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:ec2db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ec在庫取得バッチ';

    /**
     * Create a new command instance.
     *
     * @param ProductService $productService
     * @return void
     */
    public function __construct(ProductService $productService)
    {
        parent::__construct();
        $this->productService = $productService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path   = storage_path('logs/batch_product_ec2db.log');
        config(['logging.channels.daily.path' => $path]);
        if (!$this->ableToExecute()) {
            Log::info('[' . $this->getCommandName() . ']' . ' Skip this run time at ' . date('Y-m-d H:i:s'));

            return;
        }
        try {
            $this->startLog();
            $page   = 1;
            $params = [
                'limit_per_page' => config('makeshop.batch.product.limit_per_page'),
            ];
            while ($page < config('makeshop.batch.loop_limit')) {
                $params['display_page'] = $page;
                $data                   = $this->productService->search($params);
                $products               = $data['product_list'];
                foreach ($products as $product) {
                    if (!empty($product['ubrand_code'])) {
                        $this->productService->insertOrUpdate($product);
                    }
                }
                if (count($products) < $params['limit_per_page']) {
                    break;
                }
                $page++;
            }
            $this->logSuccess();
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
        } finally {
            $this->endLog();
        }
    }
}
