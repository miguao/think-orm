<?php

declare (strict_types=1);

namespace app\kernel\plugin\entity;

class Plugin
{
    public string $identifier;
    public array $info;
    public array $form;
    public array $handler;

    /**
     * 构造函数
     * @param string $identifier
     * @param array $info
     * @param array $form
     * @param array $handler
     */
    public function __construct(string $identifier, array $info, array $form, array $handler)
    {
        $this->identifier = $identifier;
        $this->info = $info;
        $this->form = $form;
        $this->handler = $handler;
    }
}