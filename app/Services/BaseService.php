<?php
namespace App\Services;

/**
 * Class BaseService
 * @package App\Service
 */
class BaseService
{
    // エラーメッセージ格納
    protected $errorMessage;
    
    /**
     * エラーメッセージを取得
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage ?? trans('messages.error.system');
    }
    
    /**
     * エラーメッセージを登録
     *
     * @param string $message
     */
    protected function setErrorMessage(string $message)
    {
        $this->errorMessage = ($message !== '') ? $message : trans('messages.error.system');
    }
}
