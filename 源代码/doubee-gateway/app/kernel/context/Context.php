<?php

declare(strict_types=1);

namespace app\kernel\context;

class Context
{
    /**
     * @var array
     */
    private static array $data = [];

    /**
     * 设置数据
     * @param string $key
     * @param $value
     * @return void
     */
    public static function set(string $key, $value): void
    {
        self::$data[$key] = $value;
    }

    /**
     * 获取数据
     * @param string $key
     * @param $default
     * @return mixed|null
     */
    public static function get(string $key, $default = null): mixed
    {
        return self::$data[$key] ?? $default;
    }

    /**
     * 是否存在
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return isset(self::$data[$key]);
    }

    /**
     * 删除数据
     * @param string $key
     * @return void
     */
    public static function delete(string $key): void
    {
        unset(self::$data[$key]);
    }

    /**
     * 清空数据
     * @return void
     */
    public static function clear(): void
    {
        self::$data = [];
    }
}