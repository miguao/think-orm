<?php

declare(strict_types=1);

namespace app\service\admin\impl;

use app\exception\JsonException;
use app\model\SystemConfig;
use app\service\admin\ConfigService;

class ConfigServiceImpl implements ConfigService
{
    public function getConfig(string $key, bool $public = false, bool $status = false): array
    {
        $config = SystemConfig::newQuery()->where('key', $key);
        if (!$status) {
            $config = $config->where('status', 1);
        }
        $config = $config->find();

        if (!$config) {
            throw new JsonException("配置项不存在");
        }
        if ($public && $config->public == 0) {
            throw new JsonException('当前配置项为内部配置项，未授权调用！');
        }

        return [
            "name" => $config->name,
            "key" => $config->key,
            "data" => $config->getConfigData()
        ];
    }
}