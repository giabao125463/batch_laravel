<?php

namespace App\Services\MakeshopApi;

use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Facades\Log;

/**
 * Class AxApiService
 * @package App\Service
 */
class ProductQueryBuilder extends BaseQueryBuilder
{
    public function __construct()
    {
        parent::__construct();
        $this->responseFormat = 'json';
    }

    /**
     * 商品データ連携機能設定画面で発行した認証コードと呼び出す機能を指定し、各商品情報取得APIにアクセスするためのURLを発行する機能です。
     * アクセスURL発行APIには下記URLに対して、POSTメソッドでパラメータを送信してください。
     *
     * @return void
     */
    public function auth()
    {
        $sessionTokenKey = 'MS_AUTH_TOKEN';
        $sessionTokenValid = 'MS_AUTH_VALID';
        if (session()->has($sessionTokenKey) && session()->has($sessionTokenValid)) {
            $now   = new \DateTime();
            $valid = \DateTime::createFromFormat('YmdHis', session($sessionTokenValid));
            $valid->modify('-1 minute');
            if ($now < $valid) {
                Log::info('Reuse Product access token: ' . session($sessionTokenKey));
                return session($sessionTokenKey);
            }
        }
        $productConfig = config('makeshop.api.product');
        $path          = $productConfig['paths']['auth'];
        $params        = [
            'auth_code' => $productConfig['token'],
            'process'   => 'search',
        ];
        $response = $this->request($path, [], $params);
        $auth     = data_get($response, 'result_data');
        if (!$auth || $auth['status_code'] != 200) {
            return false;
        }
        $params = parse_url($auth['access_url']);
        parse_str($params['query'], $query);
        session([$sessionTokenValid => $auth['expire_date']]);
        session([$sessionTokenKey => $query['access_token']]);
        session()->save();
        Log::info('Create new Product access token: ' . $query['access_token']);

        return $query['access_token'] ?? false;
    }

    /**
     * 注文日時、注文番号を指定することで対象注文を選択可能です。
     *
     * @param array $input
     * @return array
     */
    public function search($input = [])
    {
        $productConfig = config('makeshop.api.product');
        $default       = [
            'access_token' => $this->auth()
        ];
        $params   = array_merge($default, $input);
        $products = $this->request($productConfig['paths']['search'], $params, $params);

        return $products['result_data'] ?? null;
    }

    /**
     * Update product information by API
     *
     * @param $csvFile String
     * @return bool
     */
    public function updateProduct($csvFile, $isOption = false)
    {
        $uploadInfo = $this->getRegisterProductKey();
        $multiPart  = [
            ['name' => 'key', 'contents' => $uploadInfo[1]],
            ['name' => 'src', 'contents' => '1'],
            ['name' => 'dest', 'contents' => $isOption ? '2' : '1'],
            ['name' => 'upload_file', 'contents' => fopen($csvFile, 'r')],
        ];
        $response = preg_split('/\n/m', $this->uploadFile($uploadInfo[0], $multiPart));
        // Reset response format
        $this->responseFormat = 'json';
        // Check result
        if (count($response) == 5) {
            $totalRecord  = preg_split('/\t/m', $response[1])[1];
            $totalSuccess = preg_split('/\t/m', $response[2])[1];

            return ($totalRecord == $totalSuccess);
        }

        return false;
    }

    /**
     * 商品情報等を登録するには最初に商品情報登録用の一時キーを取得する必要があるので、
     * 下記のフォームにショップの主管理者のidとpw入力して送信してください。
     *
     * @return Array
     */
    private function getRegisterProductKey()
    {
        $this->responseFormat = 'raw';
        $apiConfig            = config('makeshop.api');
        $productConfig        = $apiConfig['product'];
        $path                 = $productConfig['paths']['key'];
        $params               = [
            'id' => $apiConfig['shopid'],
            'pw' => $apiConfig['shop_pass']
        ];

        return preg_split('/\n/m', $this->request($path, [], $params));
    }
}
