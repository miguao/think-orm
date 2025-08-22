<?php

declare(strict_types=1);

use app\kernel\database\command\ModelCommand;

return [
    'commands' => [
        'gen:model' => ModelCommand::class,
    ],
];
