<?php

declare(strict_types=1);

use app\plugin\AliPay\Service\Impl\TradeServiceImpl;
use app\plugin\AliPay\Service\TradeService;

return [
    TradeService::class => TradeServiceImpl::class,
];