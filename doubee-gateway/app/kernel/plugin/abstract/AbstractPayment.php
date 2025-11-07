<?php

declare (strict_types=1);

namespace app\kernel\plugin\abstract;

use app\kernel\plugin\handler\Payment;

abstract class AbstractPayment implements Payment
{
    /**
     * 配置信息
     * @var array
     */
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
}