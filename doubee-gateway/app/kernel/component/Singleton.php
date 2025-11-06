<?php

declare(strict_types=1);

namespace app\kernel\component;

trait Singleton
{
    /**
     * 实例对象
     * @var Singleton|null
     */
    protected static ?self $instance = null;

    /**
     * 获取实例对象
     * @return static
     */
    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}