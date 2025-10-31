<?php

declare (strict_types=1);

namespace app\controller\admin\api;

use app\controller\AbstractController;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\Inject;
use app\kernel\route\annotation\PostMapping;
use app\service\admin\UserService;
use think\response\Json;

#[Controller("/admin/api/auth")]
class AuthController extends AbstractController
{
    #[Inject]
    protected UserService $userService;

    #[PostMapping("login")]
    public function login(): Json
    {
        $map = $this->request->post();
        $this->validator((array)$map, [
            'email' => 'require|email',
            'password' => 'require'
        ], [
            'email.require' => '登录邮箱不能为空',
            'email.email' => '邮箱号码格式不正确',
            'password.required' => '密码不能为空',
        ]);
        $token = $this->userService->login($map['email'], $map['password']);

        return $this->json(data: ['token' => $token]);
    }
}