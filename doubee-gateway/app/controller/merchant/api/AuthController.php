<?php

declare(strict_types=1);

namespace app\controller\merchant\api;

use app\controller\AbstractMerchantController;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\Inject;
use app\kernel\route\annotation\PostMapping;
use app\service\merchant\UserService;
use think\response\Json;

#[Controller("/merchant/api/auth")]
class AuthController extends AbstractMerchantController
{
    #[Inject]
    protected UserService $userService;

    #[PostMapping("passwordLogin")]
    public function passwordLogin(): Json
    {
        $map = $this->request->post();
        $this->validator((array)$map, [
            'username' => 'require',
            'password' => 'require'
        ], [
            'username.require' => '用户名不能为空',
            'password.require' => '密码不能为空',
        ]);

        $token = $this->userService->login($map['username'], $map['password']);
        return $this->json(message: "登录成功", data: ['token' => $token]);
    }

    #[PostMapping("verificationCodeLogin")]
    public function verificationCodeLogin(): Json
    {
        $map = $this->request->post();
        $this->validator((array)$map, [
            'username' => 'require',
            'verification_code' => 'require',
        ], [
            'username.require' => '用户名不能为空',
            'verification_code.require' => '验证码不能为空',
        ]);

        $token = $this->userService->verificationCodeLogin($map['username'], $map['verification_code']);
        return $this->json(message: "登录成功", data: ['token' => $token]);
    }

    #[PostMapping("sendLoginVerificationCode")]
    public function sendLoginVerificationCode(): Json
    {
        $map = $this->request->post();
        $this->validator((array)$map, ['username' => 'require'], ['username.require' => '用户名不能为空']);

        $this->userService->sendLoginVerificationCode($map['username']);
        return $this->json(message: "验证码发送成功，有效期为 3 分钟，请尽快使用。");
    }

    #[PostMapping("emailRegister")]
    public function emailRegister(): Json
    {
        $map = $this->request->post();
        $this->validator((array)$map, [
            'email' => 'require|email',
            'password' => 'require|min:6',
            'confirm_password' => 'require|confirm:password',
            'verification_code' => 'require|length:6',
        ], [
            'email.require' => '邮箱号码不能为空',
            'email.email' => '邮箱格式不正确',
            'password.require' => '登录密码不能为空',
            'password.min' => '登录密码不能少于6位',
            'confirm_password.require' => '确认密码不能为空',
            'confirm_password.confirm' => '两次输入的密码不一致',
            'verification_code.require' => '验证码不能为空',
            'verification_code.length' => '验证码错误',
        ]);

        $token = $this->userService->emailRegister($map['email'], $map['password'], $map['verification_code']);
        return $this->json(message: "注册成功", data: ['token' => $token]);
    }

    #[PostMapping("phoneRegister")]
    public function phoneRegister(): Json
    {
        $map = $this->request->post();
        $this->validator((array)$map, [
            'phone' => 'require|mobile',
            'password' => 'require|min:6',
            'confirm_password' => 'require|confirm:password',
            'verification_code' => 'require|length:6',
        ], [
            'phone.require' => '手机号码不能为空',
            'phone.mobile' => '手机号码格式不正确',
            'password.require' => '登录密码不能为空',
            'password.min' => '登录密码不能少于6位',
            'confirm_password.require' => '确认密码不能为空',
            'confirm_password.confirm' => '两次输入的密码不一致',
            'verification_code.require' => '验证码不能为空',
            'verification_code.length' => '验证码错误',
        ]);

        $token = $this->userService->phoneRegister($map['phone'], $map['password'], $map['verification_code']);
        return $this->json(message: "注册成功", data: ['token' => $token]);
    }

    #[PostMapping("sendRegisterCode")]
    public function sendRegisterCode(): Json
    {
        $map = $this->request->post();
        $this->validator((array)$map, ['username' => 'require'], ['username.require' => '用户名不能为空']);

        $this->userService->sendRegisterCode($map['username']);
        return $this->json(message: "验证码发送成功，有效期为 3 分钟，请尽快使用。");
    }

    #[PostMapping("resetPassword")]
    public function resetPassword(): Json
    {
        $map = $this->request->post();
        $this->validator((array)$map, [
            'username' => 'require',
            'password' => 'require|min:6',
            'confirm_password' => 'require|confirm:password',
            'verification_code' => 'require|length:6',
        ], [
            'username.require' => '用户名不能为空',
            'password.require' => '登录密码不能为空',
            'password.min' => '登录密码不能少于6位',
            'confirm_password.require' => '确认密码不能为空',
            'confirm_password.confirm' => '两次输入的密码不一致',
            'verification_code.require' => '验证码不能为空',
            'verification_code.length' => '验证码错误',
        ]);

        $this->userService->resetPassword($map['username'], $map['password'], $map['verification_code']);
        return $this->json(message: "密码重置成功，请使用新密码重新登录。");
    }

    #[PostMapping("sendResetPasswordCode")]
    public function sendResetPasswordCode(): Json
    {
        $map = $this->request->post();
        $this->validator((array)$map, ['username' => 'require'], ['username.require' => '用户名不能为空']);

        $this->userService->sendResetPasswordCode($map['username']);
        return $this->json(message: "验证码发送成功，有效期为 3 分钟，请尽快使用。");
    }
}