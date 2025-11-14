<?php

declare(strict_types=1);

namespace app\plugin\AliPay\Handler;

use app\kernel\plugin\abstract\AbstractPayment;
use app\kernel\plugin\entity\Payment as PaymentEntity;
use app\kernel\plugin\exception\PluginException;
use EasyAlipay\Builder;
use EasyAlipay\Crypto\Rsa;

class Payment extends AbstractPayment
{
    public function create(): PaymentEntity
    {
        $payment = new PaymentEntity();
        $instance = Builder::factory([
            'privateKey' => Rsa::fromPkcs1($this->config['apply_private_key']),
            'publicKey' => Rsa::fromSpki($this->config['alipay_public_key']),
            'params' => [
                'app_id' => $this->config['app_id'],
            ],
        ]);

        $request = $instance->chain('alipay.trade.precreate')->post([
            'out_trade_no' => $this->order->trade_no,
            'subject' => $this->order->subject,
            'total_amount' => $this->amount,
        ], ['query' => [
            'notify_url' => $this->notificationUrl
        ]]);
        $responseBody = $request->getBody();
        $responseData = json_decode($responseBody->getContents(), true);
        if ($responseData['code'] == 10000 && isset($responseData['qr_code'])) {
            $payment->setPayUrl($responseData['qr_code']);
            return $payment;
        }

        throw new PluginException("下单失败，请稍后重试。");
    }

    public function async()
    {
    }
}