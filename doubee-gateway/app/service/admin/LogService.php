<?php

declare(strict_types=1);

namespace app\service\admin;

use app\model\SystemUser;

interface LogService
{
    /**
     * 创建登录日志
     * @param SystemUser $user
     * @param string $loginIp
     * @param string $userAgent
     * @return void
     */
    public function createLoginLog(SystemUser $user, string $loginIp, string $userAgent): void;
}