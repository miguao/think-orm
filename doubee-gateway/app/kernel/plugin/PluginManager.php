<?php

declare (strict_types=1);

namespace app\kernel\plugin;

use think\facade\Event;

class PluginManager
{
    public static function trigger(string $hook, array $params = []): void
    {
        Event::trigger('PluginEvent', ['hook' => $hook, 'params' => $params]);
    }
}