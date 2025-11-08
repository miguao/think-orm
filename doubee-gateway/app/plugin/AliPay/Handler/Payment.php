<?php

declare(strict_types=1);

namespace app\plugin\AliPay\Handler;

use app\kernel\plugin\abstract\AbstractPayment;
use EasyAlipay\Builder;
use EasyAlipay\Crypto\Rsa;

class Payment extends AbstractPayment
{
    public function create()
    {
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
        return $responseData;
    }

    public function async()
    {
    }
}