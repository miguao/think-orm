<?php

declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\relation\HasOne;

/**
 * @property int $id
 * @property int $merchant_id
 * @property int $bank_id
 * @property string $card_no
 * @property string $card_image
 * @property string $card_image_hash
 * @property string $creation_time
 * @property int $status
 */
class MerchantBankCard extends Model
{
    /**
     * 类型信息
     * @return HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(PaymentType::class, 'id', 'type_id');
    }

    /**
     * 商户信息（持卡人）
     * @return HasOne
     */
    public function merchant(): HasOne
    {
        return $this->hasOne(Merchant::class, 'id', 'merchant_id');
    }
}