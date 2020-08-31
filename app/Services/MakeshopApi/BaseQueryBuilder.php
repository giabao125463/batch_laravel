<?php

namespace App\Services\MakeshopApi;

use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Facades\Log;
use Orchestra\Parser\Xml\Facade as XmlParser;

/**
 * Class AxApiService
 * @package App\Service
 */
class BaseQueryBuilder
{
    protected $shopId;
    protected $token;
    protected $responseFormat;

    public function __construct()
    {
        $this->client = new Guzzle([
            'base_uri' => config('makeshop.api.url'),
        ]);
        $this->shopId         = config('makeshop.api.shopid');
        $this->apiResponse    = [];
        $this->responseFormat = 'xml';
    }

    /**
     * APIコール
     *
     * @param string $uri
     * @param array $params
     * @param string $method
     * @param mixed $body
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return \Illuminate\Support\Collection
     */
    public function request($uri, $params, $body = [], $method = 'POST')
    {
        usleep(config('makeshop.api.default_sleep'));
        Log::debug($uri, $params);
        // Params
        $defaultParams['shop_id']         = $this->shopId;
        $defaultParams['response_format'] = $this->responseFormat;
        $params                           = array_merge($defaultParams, $params);

        // Body
        $defaultBody['shop_id']         = $this->shopId;
        $defaultBody['response_format'] = $this->responseFormat;
        $body                           = array_merge($defaultBody, $body);

        // TODO: Add more header when needed
        $headers = [];

        // エラーがあればThrowする
        $response = $this->client->request($method, $uri, [
            'headers'     => $headers,
            'http_errors' => false,
            'query'       => $params,
            'form_params' => $body,
        ]);
        $contents = $this->setApiResponse($response, $params);

        return $contents ?? null;
    }

    /**
     * APIの実行結果を取得
     *
     * @param string $key 取得対象（status|response|request）
     * @return array|string
     */
    protected function getApiResponse($key = null)
    {
        return ($key !== null) ? data_get($this->apiResponse, $key, '') : $this->apiResponse;
    }

    /**
     * APIの実行結果を設定
     *
     * @param GuzzleHttp\Psr7\Response $response
     * @param array $request
     * @return string
     */
    private function setApiResponse($response, $request)
    {
        $responseBody = (string) $response->getBody();
        switch($this->responseFormat) {
            case 'xml':
                $data = $this->xmlDecode($responseBody);
                break;
            case 'json':
                $data = json_decode($responseBody, true);
                break;
            case 'raw':
            default:
                $data = $responseBody;
                break;
        }

        data_set($this->apiResponse, 'request', json_encode($request, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        data_set($this->apiResponse, 'status', $response->getStatusCode());
        data_set($this->apiResponse, 'response', $data);

        return $this->getApiResponse('response');
    }

    /**
     * XML Decode
     *
     * @param string $content
     * @return array
     */
    protected function xmlDecode($content)
    {
        $xml = simplexml_load_string($content);

        return json_decode(json_encode($xml), true);
    }

    /**
     * Upload file to API
     *
     * @param $uri String
     * @param $multiPart Array
     */
    public function uploadFile($uri, $multiPart = [])
    {
        usleep(config('makeshop.api.default_sleep'));
        Log::debug($uri, $multiPart);
        // TODO: Add more header when needed
        $headers = [];

        // エラーがあればThrowする
        $response = $this->client->request('POST', $uri, [
            'headers'     => $headers,
            'http_errors' => false,
            'multipart'   => $multiPart,
        ]);

        return (string) $response->getBody();
    }
}
