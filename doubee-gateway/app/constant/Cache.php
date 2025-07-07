<?php

declare(strict_types=1);

namespace app\constant;

enum Cache: string
{
    # 登录验证码缓存键
    case LOGIN_VERIFICATION_CODE = "auth:login:code:%s";
    # 注册验证码缓存键
    case REGISTER_VERIFICATION_CODE = "auth:register:code:%s";
    # 找回密码验证码缓存键
    case RESET_PASSWORD_VERIFICATION_CODE = "auth:reset_password:code:%s";
}