<?php

declare (strict_types=1);

namespace app\service\admin\impl;

use app\exception\JsonException;
use app\model\SystemLoginLog;
use app\model\SystemUser;
use app\service\admin\LogService;
use app\utils\DateUtils;

class LogServiceImpl implements LogService
{
    public function createLoginLog(SystemUser $user, string $loginIp, string $userAgent): void
    {
        try {
            SystemLoginLog::insert([
                'user' => $user->toArray(),
                'login_ip' => $loginIp,
                'location' => '四川省',
                'user_agent' => $userAgent,
                'creation_time' => DateUtils::current(),
            ]);
        } catch (\Exception $exception) {
            throw new JsonException("登录失败：{$exception->getMessage()}");
        }
    }
}