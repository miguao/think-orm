<?php

declare(strict_types=1);

use app\kernel\database\command\ModelCommand;
use app\kernel\route\command\RouteCacheCommand;
use app\kernel\route\command\RouteClearCommand;

return [
    'commands' => [
        'gen:model' => ModelCommand::class,
        'route:cache' => RouteCacheCommand::class,
        'route:clear' => RouteClearCommand::class,
    ],
];
