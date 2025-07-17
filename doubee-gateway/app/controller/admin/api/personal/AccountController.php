<?php

declare(strict_types=1);

namespace app\controller\admin\api\personal;

use app\controller\AbstractAdminController;
use app\middleware\admin\AuthenticationMiddleware;
use app\service\admin\UserService;
use think\annotation\Inject;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/admin/api/personal/account")]
#[Middleware(AuthenticationMiddleware::class)]
class AccountController extends AbstractAdminController
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