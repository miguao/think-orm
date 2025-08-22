<?php

declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\relation\BelongsToMany;

/**
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string $salting
 * @property string $login_time
 * @property string $last_login_time
 * @property string $login_ip
 * @property string $last_login_ip
 * @property string $login_ua
 * @property string $last_login_ua
 * @property string $creation_time
 * @property int $status
 */
class SystemUser extends Model
{
    /**
     * 角色列表
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(SystemUserRole::class, 'system_user_role_relation', 'role_id', 'user_id');
    }
}