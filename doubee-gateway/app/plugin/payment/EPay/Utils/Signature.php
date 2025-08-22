<?php

declare(strict_types=1);

namespace app\plugin\payment\EPay\Utils;

class Signature
{
    /**
     * 生成签名
     * @param array $data
     * @param string $key
     * @return string
     */
    public static function generateSignature(array $data, string $key): string
    {
        ksort($data);
        $sign = '';
        foreach ($data as $k => $v) {
            $sign .= $k . '=' . $v . '&';
        }
        $sign = trim($sign, '&');

        return md5($sign . $key);
    }

    /**
     * 验证签名
     * @param array $data
     * @param array $config
     * @return bool
     */
    public function verification(array $data, array $config): bool
    {
        $sign = $data['sign'];
        unset($data['sign']);
        unset($data['sign_type']);
        $generateSignature = self::generateSignature($data, $config['key']);
        if ($sign != $generateSignature) {
            return false;
        }
        return true;
    }
}