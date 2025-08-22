<?php

declare(strict_types=1);

namespace app\service\admin;

interface ConfigService
{
    /**
     * 获取系统配置
     * @param string $key
     * @param bool $public
     * @param bool $status
     * @return array
     */
    public function getConfig(string $key, bool $public = false, bool $status = false): array;
}