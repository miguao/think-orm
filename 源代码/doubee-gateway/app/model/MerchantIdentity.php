<?php

declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\relation\HasOne;

/**
 * @property int $id
 * @property int $merchant_id
 * @property string $name
 * @property string $id_card
 * @property int $type
 * @property string $creation_time
 * @property string $review_time
 * @property int $status
 */
class MerchantIdentity extends Model
{
    /**
     * 商户信息
     * @return HasOne
     */
    public function merchant(): HasOne
    {
        return $this->hasOne(Merchant::class, 'id', 'merchant_id');
    }
}