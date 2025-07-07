<?php

declare (strict_types=1);

namespace app\service\admin;

use app\model\SystemUser;

interface UserService
{
    /**
     * 登录
     * @param string $email
     * @param string $password
     * @return string
     */
    public function login(string $email, string $password): string;

    /**
     * 设置登录成功，并且返回JWT
     * @param SystemUser $user
     * @return string
     */
    public function setLoginSuccess(SystemUser $user): string;

    /**
     * 获取权限
     * @param int $userId
     * @return array
     */
    public function findByUserPermissions(int $userId): array;

    /**
     * 退出登录
     * @param int $userId
     * @return void
     */
    public function logout(int $userId): void;
}