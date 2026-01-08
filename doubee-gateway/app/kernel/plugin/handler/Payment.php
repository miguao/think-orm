<?php

declare (strict_types=1);

namespace app\kernel\plugin\handler;

use app\kernel\plugin\entity\Payment as PaymentEntity;
use think\Response;

interface Payment
{
    /**
     * 创建订单
     * @return PaymentEntity
     */
    public function create(): PaymentEntity;

    /**
     * 异步回调
     * @return Response
     */
    public function async(): Response;
}