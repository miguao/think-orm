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
        $user['authorities'] = [
            [
                'menuId' => 1,
                'parentId' => 0,
                'title' => '首页',
                'path' => '/dashboard/workplace',
                'component' => '/dashboard/workplace',
                'menuType' => 0,
                'icon' => 'IconElHouse',
                'hide' => 0,
            ],
            [
                'menuId' => 3,
                'parentId' => 0,
                'title' => '账户资料',
                'path' => '/user/profile',
                'component' => '/user/profile',
                'menuType' => 0,
                'icon' => 'IconElUser',
                'hide' => 0,
            ],
            [
                'menuId' => 4,
                'parentId' => 0,
                'title' => '提现管理',
                'path' => '/finance/withdraw',
                'component' => '/personal/info',
                'menuType' => 0,
                'icon' => 'IconElMoney',
                'hide' => 0,
            ],
            [
                'menuId' => 5,
                'parentId' => 0,
                'title' => '账单明细',
                'path' => '/finance/bills',
                'component' => '/personal/info',
                'menuType' => 0,
                'icon' => 'IconElDocument',
                'hide' => 0,
            ],
            [
                'menuId' => 7,
                'parentId' => 0,
                'title' => '应用管理',
                'path' => '/application/list',
                'component' => '/personal/info',
                'menuType' => 0,
                'icon' => 'IconElCpu',
                'hide' => 0,
            ],
            [
                'menuId' => 8,
                'parentId' => 0,
                'title' => '订单管理',
                'path' => '/orders/list',
                'component' => '/personal/info',
                'menuType' => 0,
                'icon' => 'IconElList',
                'hide' => 0,
            ],
            [
                'menuId' => 9,
                'parentId' => 0,
                'title' => '操作日志',
                'path' => '/system/logs',
                'component' => '/personal/info',
                'menuType' => 0,
                'icon' => 'IconElNotebook',
                'hide' => 0,
            ],
        ];
        $user['nickname'] = $user['email'] ?? $user['phone'];
        return $this->json(data: $user);
    }

    #[Route("POST", "logout")]
    public function logout(): Json
    {
        $this->userService->logout($this->getUserId());
        return $this->json(message: "退出成功");
    }
}