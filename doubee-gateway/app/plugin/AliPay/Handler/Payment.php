<?php

declare(strict_types=1);

namespace app\plugin\AliPay\Handler;

use app\kernel\plugin\abstract\AbstractPayment;

class Payment extends AbstractPayment
{
    public function create()
    {
        print_r($this->config);
        exit;
    }

    public function async()
    {
    }
}