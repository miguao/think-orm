<?php

declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\relation\HasOne;

/**
 * @property int $id
 * @property int $role_id
 * @property int $merchant_no
 * @property string $phone
 * @property string $password
 * @property string $salting
 * @property string $email
 * @property string $creation_time
 * @property string $balance
 * @property string $freeze_balance
 * @property int $status
 */
class Merchant extends Model
{
    /**
     * 商户组信息
     * @return HasOne
     */
    public function role(): HasOne
    {
        return $this->hasOne(MerchantGroup::class, 'id', 'role_id');
    }
}