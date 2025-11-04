<?php

declare(strict_types=1);

namespace app\plugin\AliPay\Handler;

use app\kernel\plugin\abstract\AbstractPayment;
use app\plugin\AliPay\Service\TradeService;

class Payment extends AbstractPayment
{
    public function __construct(protected TradeService $tradeService)
    {
    }

    public function create()
    {
        return $this->tradeService->preCreate();
    }

    public function async()
    {
    }
}