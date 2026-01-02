<?php

declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\relation\HasOne;

class PaymentOrder extends Model
{
    /**
     * 商户信息
     * @return HasOne
     */
    public function merchant(): HasOne
    {
        return $this->hasOne(Merchant::class, "id", "merchant_id");
    }

    /**
     * 应用信息
     * @return HasOne
     */
    public function application(): HasOne
    {
        return $this->hasOne(MerchantApplication::class, "id", "application_id");
    }

    /**
     * 通道信息
     * @return HasOne
     */
    public function channel(): HasOne
    {
        return $this->hasOne(PaymentChannel::class, "id", "channel_id");
    }

    /**
     * 类型信息
     * @return HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(PaymentType::class, "id", "type_id");
    }
}