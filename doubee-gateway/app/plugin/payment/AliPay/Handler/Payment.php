<?php

declare(strict_types=1);

namespace app\plugin\payment\AliPay\Handler;

use Alipay\OpenAPISDK\Api\AlipayTradeApi;
use Alipay\OpenAPISDK\ApiException;
use Alipay\OpenAPISDK\Model\AlipayTradePrecreateModel;
use Alipay\OpenAPISDK\Util\AlipayConfigUtil;
use Alipay\OpenAPISDK\Util\AlipayLogger;
use Alipay\OpenAPISDK\Util\Model\AlipayConfig;
use app\exception\JsonException;
use app\kernel\plugin\abstract\AbstractPayment;

class Payment extends AbstractPayment
{
    public function create()
    {
        $instance = new AlipayTradeApi();

        $config = new AlipayConfig();
        $config->setAppId($this->business['app_id']);
        $config->setPrivateKey($this->business['apply_private_key']);
        $config->setAlipayPublicKey($this->business['alipay_public_key']);

        AlipayLogger::setNeedEnableLogger(false);

        $util = new AlipayConfigUtil($config);
        $instance->setAlipayConfigUtil($util);

        $model = new AlipayTradePrecreateModel();
        $model->setOutTradeNo($this->order->trade_no);
        $model->setTotalAmount((float)$this->order->amount);
        $model->setSubject($this->order->subject);
        $model->setNotifyUrl("http://qyou.nanoa.cn//");

        try {
            $response = $instance->precreate($model);
            return $response->getQrCode();
        } catch (ApiException $exception) {
            $body = json_decode($exception->getResponseBody(), true);
            throw new JsonException($body['message']);
        }
    }

    public function async()
    {
        // TODO: Implement async() method.
    }
}