<?php

declare(strict_types=1);

use app\kernel\plugin\subscribe\PluginEventSubscriber;

return [
    'bind' => [],
    'listen' => [
        'AppInit' => [],
        'HttpRun' => [],
        'HttpEnd' => [],
        'LogLevel' => [],
        'LogWrite' => [],
    ],
    'subscribe' => [
        PluginEventSubscriber::class,
    ],
];
