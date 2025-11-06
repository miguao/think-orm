<?php

declare (strict_types=1);

namespace app\kernel\plugin\entity;

class Plugin
{
    public string $identifier;
    public array $info;
    public array $handler;

    /**
     * 构造函数
     * @param string $identifier
     * @param array $info
     * @param array $handler
     */
    public function __construct(string $identifier, array $info, array $handler)
    {
        $this->identifier = $identifier;
        $this->info = $info;
        $this->handler = $handler;
    }
}