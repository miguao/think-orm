<?php

declare(strict_types=1);

namespace app\kernel\plugin\abstract;

use app\kernel\plugin\constant\Payment;
use app\model\PaymentOrder;

abstract class AbstractPayment implements Payment
{
    /**
     * 订单信息
     * @var PaymentOrder
     */
    public PaymentOrder $order;

    /**
     * 配置信息
     * @var array
     */
    public array $config = [];

    /**
     * 产品代码
     * @var string
     */
    public string $code;
}