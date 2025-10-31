<?php

declare(strict_types=1);

namespace app\plugin\AliPay\Handler;

use app\plugin\AliPay\Service\TradeService;

class Payment
{
    public function __construct(protected TradeService $tradeService)
    {
    }

    public function trade()
    {
        return $this->tradeService->preCreate();
    }
}