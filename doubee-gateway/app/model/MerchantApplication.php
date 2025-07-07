<?php

declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\relation\HasOne;

/**
 * @property int $id
 * @property int $application_id
 */
class MerchantApplication extends Model
{
    /**
     * 商户信息
     * @return HasOne
     */
    public function merchant(): HasOne
    {
        return $this->hasOne(Merchant::class, "id", "merchant_id");
    }
}