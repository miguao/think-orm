<?php

declare (strict_types=1);

namespace app\kernel\plugin\subscribe;

use think\Event;

class PluginEventSubscriber
{
    /**
     * 监听插件事件
     * @param Event $event
     * @return void
     */
    public function subscribe(Event $event): void
    {
        $event->listen('PluginEvent', [$this, 'onPluginEvent']);
    }

    public function onPluginEvent(array $payload)
    {
    }
}