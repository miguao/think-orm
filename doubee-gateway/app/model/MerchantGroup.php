<?php

declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\relation\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string $creation_time
 * @property int $status
 */
class MerchantGroup extends Model
{
    /**
     * 权限列表
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(MerchantPermission::class, 'merchant_permission_relation', 'permission_id', 'group_id');
    }
}