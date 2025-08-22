<?php

declare(strict_types=1);

namespace app\controller\merchant\api\personal;

use app\controller\AbstractMerchantController;
use app\middleware\merchant\AuthenticationMiddleware;
use app\service\admin\UserService;
use think\annotation\Inject;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/merchant/api/personal/account")]
#[Middleware(AuthenticationMiddleware::class)]
class AccountController extends AbstractMerchantController
{
    #[Inject]
    protected UserService $userService;

    #[Route("GET", "getMeInfo")]
    public function getMeInfo(): Json
    {
        $user = $this->getUserInfo();
        $permissions = $this->userService->findByUserPermissions($this->getUserId());
        $user['menus'] = $permissions['menus'];

        return $this->json(data: $user);
    }

    #[Route("POST", "logout")]
    public function logout(): Json
    {
        $this->userService->logout($this->getUserId());
        return $this->json(message: "退出成功");
    }
}