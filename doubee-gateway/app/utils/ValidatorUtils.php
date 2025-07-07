<?php

declare(strict_types=1);

namespace app\utils;

class ValidatorUtils
{
    /**
     * 验证是否为合法的手机号
     * @param string $phone
     * @return bool
     */
    public static function isValidPhoneNumber(string $phone): bool
    {
        return preg_match('/^1[3-9]\d{9}$/', $phone) === 1;
    }

    /**
     * 验证是否为合法的邮箱地址
     * @param string $email
     * @return bool
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 是否是一个合规的JSON
     * @param $string
     * @return bool
     */
    public static function isJson($string): bool
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}