<?php

declare(strict_types=1);

namespace app\controller\merchant\api\personal;

use app\controller\AbstractMerchantController;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Inject;
use app\kernel\route\annotation\Middleware;
use app\kernel\route\annotation\PostMapping;
use app\middleware\merchant\AuthenticationMiddleware;
use app\service\admin\UserService;
use think\response\Json;

#[Controller("/merchant/api/personal/account")]
#[Middleware(AuthenticationMiddleware::class)]
class AccountController extends AbstractMerchantController
{
    #[Inject]
    protected UserService $userService;

    #[GetMapping("getMeInfo")]
    public function getMeInfo(): Json
    {
        $user = $this->getUserInfo();
        $permissions = $this->userService->findByUserPermissions($this->getUserId());
        $user['menus'] = $permissions['menus'];

        return $this->json(data: $user);
    }

    #[PostMapping("logout")]
    public function logout(): Json
    {
        $this->userService->logout($this->getUserId());
        return $this->json(message: "退出成功");
    }
}