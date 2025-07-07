<?php

declare(strict_types=1);

namespace app\plugin\payment\EPay\Handler;

use app\exception\JsonException;
use app\kernel\plugin\abstract\AbstractPayment;
use app\plugin\payment\EPay\Utils\Signature;

class Payment extends AbstractPayment
{
    public function create()
    {
        if (!$this->config['merchant_id']) {
            throw new JsonException("商户号不能为空");
        }

        if (!$this->config['merchant_key']) {
            throw new JsonException("商户密钥不能为空");
        }

        if (!$this->config['gateway']) {
            throw new JsonException("网关地址不能为空");
        }

        $param = [
            'pid' => $this->config['merchant_id'],
            'type' => $this->code,
            'out_trade_no' => $this->order->trade_no,
            'notify_url' => 'https://testing.nanoa.cn/',
            'return_url' => 'https://testing.nanoa.cn/',
            'name' => $this->order->subject,
            'money' => (float)$this->order->amount,
        ];
        $param['sign'] = Signature::generateSignature($param, $this->config['merchant_key']);
        $param['sign_type'] = 'MD5';

        $url = trim($this->config['gateway'], "/") . "/submit.php?" . http_build_query($param);
    }

    public function async()
    {
        // TODO: Implement async() method.
    }
}