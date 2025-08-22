<?php

declare(strict_types=1);

namespace app\utils;

class AesUtils
{
    private static string $key = "a68c3b66701aa3a7";

    private static string $iv = "bab70271277b57c7";

    /**
     * AES->CBC 加密
     * @param mixed $data
     * @param string|null $key
     * @param string|null $iv
     * @return string
     */
    public static function encrypt(mixed $data, string $key = null, string $iv = null): string
    {
        if (empty($key)) $key = self::$key;
        if (empty($iv)) $iv = self::$iv;

        return base64_encode(openssl_encrypt(serialize($data), 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv));
    }

    /**
     * AES->CBC 解密
     * @param string $data
     * @param string|null $key
     * @param string|null $iv
     * @return mixed
     */
    public static function decrypt(string $data, string $key = null, string $iv = null): mixed
    {
        if (empty($key)) $key = self::$key;
        if (empty($iv)) $iv = self::$iv;

        return unserialize((string)openssl_decrypt(base64_decode($data), 'aes-128-cbc', $key, 1, $iv));
    }
}