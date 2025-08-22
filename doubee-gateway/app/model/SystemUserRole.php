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
class SystemUserRole extends Model
{
    /**
     * 权限列表
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(SystemUserPermission::class, 'system_user_permission_relation', 'permission_id', 'role_id');
    }
}