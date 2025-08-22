<?php

declare (strict_types=1);

namespace app\constant;

enum StatusCode: int
{
    case SUCCESS = 200;
    case FAILED = 100;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
    case NOTFOUND = 404;
    case SERVER_ERROR = 500;

    /**
     * 获取消息
     * @return string
     */
    public function getMessage(): string
    {
        return match ($this) {
            self::SUCCESS => '获取成功',
            self::FAILED => '操作失败',
            self::UNAUTHORIZED => '未授权访问',
            self::FORBIDDEN => '服务器拒绝访问请求',
            self::NOTFOUND => '网页或文件未找到',
            self::SERVER_ERROR => '服务器内部错误',
        };
    }
}