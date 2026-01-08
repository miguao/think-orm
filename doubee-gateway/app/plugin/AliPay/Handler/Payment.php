<?php

declare(strict_types=1);

namespace app\plugin\AliPay\Handler;

use Alipay\EasySDK\Kernel\Config;
use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;
use app\kernel\plugin\abstract\AbstractPayment;
use app\kernel\plugin\entity\Payment as PaymentEntity;
use app\kernel\plugin\exception\PluginException;
use Exception;
use think\Response;

class Payment extends AbstractPayment
{
    public function create(): PaymentEntity
    {
        $config = new Config();
        $config->protocol = 'https';
        $config->gatewayHost = 'openapi.alipay.com';
        $config->signType = 'RSA2';
        $config->appId = $this->config['app_id'];
        $config->merchantPrivateKey = $this->config['apply_private_key'];
        $config->alipayPublicKey = $this->config['alipay_public_key'];

        Factory::setOptions($config);

        try {
            $request = Factory::payment()->faceToFace()
                ->asyncNotify($this->notificationUrl)
                ->preCreate($this->order->subject, $this->order->trade_no, $this->amount);

            if ((new ResponseChecker())->success($request)) {
                $payment = new PaymentEntity();
                $payment->setPayUrl($request->qrCode);
                return $payment;
            }
        } catch (Exception $exception) {
            throw new PluginException($exception->getMessage());
        }

        throw new PluginException("下单失败，请稍后重试。");
    }

    public function async(): Response
    {
        $map = request()->all();

        $config = new Config();
        $config->protocol = 'https';
        $config->gatewayHost = 'openapi.alipay.com';
        $config->signType = 'RSA2';
        $config->appId = $this->config['app_id'];
        $config->merchantPrivateKey = $this->config['apply_private_key'];
        $config->alipayPublicKey = $this->config['alipay_public_key'];

        Factory::setOptions($config);

        if (Factory::payment()->common()->verifyNotify($map)) {
            $this->successful();
            return Response::create('success', 'text');
        }

        return Response::create('fail', 'text');
    }
}