<?php

declare(strict_types=1);

namespace app\service\merchant;

use app\model\Merchant;

interface UserService
{
    /**
     * 用户登录
     * @param string $username
     * @param string $password
     * @return string
     */
    public function login(string $username, string $password): string;

    /**
     * 验证码登录
     * @param string $username
     * @param string $code
     * @return string
     */
    public function verificationCodeLogin(string $username, string $code): string;

    /**
     * 发送登录验证码
     * @param string $username
     * @return void
     */
    public function sendLoginVerificationCode(string $username): void;

    /**
     * 设置登录成功，并且返回JWT
     * @param Merchant $merchant
     * @return string
     */
    public function setLoginSuccess(Merchant $merchant): string;

    /**
     * 邮箱注册
     * @param string $email
     * @param string $password
     * @param string $code
     * @return string
     */
    public function emailRegister(string $email, string $password, string $code): string;

    /**
     * 手机号注册
     * @param string $phone
     * @param string $password
     * @param string $code
     * @return string
     */
    public function phoneRegister(string $phone, string $password, string $code): string;

    /**
     * 发送注册验证码
     * @param string $username
     * @return void
     */
    public function sendRegisterCode(string $username): void;

    /**
     * 重置密码
     * @param string $username
     * @param string $password
     * @param string $code
     * @return void
     */
    public function resetPassword(string $username, string $password, string $code): void;

    /**
     * 发送找回验证码
     * @param string $username
     * @return void
     */
    public function sendResetPasswordCode(string $username): void;

    /**
     * 创建关联应用
     * @param Merchant $merchant
     * @return bool
     */
    public function createWithApplication(Merchant $merchant): bool;

    /**
     * 退出登录
     * @param int $userId
     * @return void
     */
    public function logout(int $userId): void;
}