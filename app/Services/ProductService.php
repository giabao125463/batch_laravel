<?php

namespace App\Services;

use App\Helpers\StringHelper;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\MakeshopApi\ProductQueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ProductService extends BaseService
{

    /** @var ProductQueryBuilder */
    private $productQueryBuilder;
    private $productRepository;

    /**
     * ProductService constructor.
     *
     * @param ProductQueryBuilder $productQueryBuilder
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductQueryBuilder $productQueryBuilder, ProductRepository $productRepository)
    {
        $this->productQueryBuilder = $productQueryBuilder;
        $this->productRepository   = $productRepository;
    }

    /**
     * Search product by API
     *
     * @param $input Array
     *
     * @return String
     */
    public function search($input)
    {
        return $this->productQueryBuilder->search($input);
    }

    /**
     * Insert or update product
     *
     * @param [type] $data
     * @return void
     */
    public function insertOrUpdate($data)
    {
        foreach ($data as $name => $value) {
            $data[$name] = $value ?? null;
        }
        $data['jancode'] = StringHelper::removeWhiteSpace($data['jancode']);
        try {
            $ubrandCode = $data['ubrand_code'];
            Log::info('Start updating product from EC. ubrand_code=' . $ubrandCode);
            $product = $this->productRepository->find($ubrandCode);
            if ($product) {
                Log::info('Update product stock');
                $product = $this->productRepository->update(['stock' => $data['stock']], $product->ubrand_code);
            } else {
                $product = $this->productRepository->create($data);
                Log::info('Create new product record in database');
            }
            $option1 = data_get($data, 'option.option_name1');
            $option2 = data_get($data, 'option.option_name2');
            $selectOptions = data_get($data, 'option.select_options');
            if (!empty($option1) || !empty($option2)) {
                if (empty($selectOptions)) {
                    Log::info('Call API to get opions detail');
                    $response = $this->search(['ubrand_code' => $ubrandCode]);
                    $detail = collect(data_get($response, 'product_list'))->first();
                    $selectOptions = data_get($detail, 'option.select_options');
                }
                if (!empty($selectOptions)) {
                    $this->insertOrUpdateProductOptions($product, $selectOptions);
                }
            }
            Log::info('End updating/creating product');
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
        }
    }

    /**
     * Update product information by API
     *
     * @param $csvFile String
     * @return bool
     */
    public function updateProduct($csvFile, $isOption = false)
    {
        return $this->productQueryBuilder->updateProduct($csvFile, $isOption);
    }

    /**
     * Get Product information from Database
     *
     * @param $scanCode string
     *
     * @return model
     */
    public function getProductByScanCode($scanCode)
    {
        return $this->productRepository->find($scanCode);
    }

    /**
     * Find product by jancode
     *
     * @param string $jancode
     * @return Product
     */
    public function findByJancode($jancode)
    {
        $product = $this->productRepository->all(['jancode' => $jancode])
            ->whereNotNull('parent_code')
            ->first();
        if ($product) {
            return $product;
        }
        $product = $this->productRepository->all(['jancode' => $jancode])
            ->whereNull('parent_code')
            ->first();
        return $product;
    }

    /**
     * Create/Update product options
     *
     * @param Product $parent
     * @param array $options
     * @return void
     */
    public function insertOrUpdateProductOptions(Product $parent, $options)
    {
        Log::info('    Start creating/updating product options');
        foreach($options as $option) {
            $data = [
                'parent_code' => $parent->ubrand_code,
                'option_id' => $option['option_id'],
                'ubrand_code' => $option['option_ubrand_code'],
                'price' => (int) $option['price'],
                'stock' => $option['stock'],
                'jancode' => StringHelper::removeWhiteSpace($option['jancode']),
                'member_group_prices' => $option['member_group_prices'] ?? null,
            ];
            $product = $this->productRepository->find($data['ubrand_code']);
            if ($product) {
                Log::info('    Update stock. ubrand_code=' . $data['ubrand_code']);
                $product =$this->productRepository->update($data, $data['ubrand_code']);
            } else {
                Log::info('    Create new product option. ubrand_code=' . $data['ubrand_code']);
                $product = $this->productRepository->create($data);
            }
        }
    }
}
