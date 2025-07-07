<?php

declare (strict_types=1);

namespace app\controller\admin\api;

use app\controller\AbstractController;
use app\model\SystemLoginLog;
use app\service\admin\UserService;
use think\annotation\Inject;
use think\annotation\route\Group;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/admin/api/auth")]
class AuthController extends AbstractController
{
    #[Inject]
    protected UserService $userService;

    #[Route("POST", "login")]
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