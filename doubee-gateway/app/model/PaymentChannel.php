<?php

declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\relation\HasOne;

class PaymentChannel extends Model
{
    /**
     * 银行信息
     * @return HasOne
     */
    public function bank(): HasOne
    {
        return $this->hasOne(PaymentBank::class, "id", "bank_id");
    }
}