<?php

declare(strict_types=1);

namespace app\controller\admin\api\merchant;

use app\controller\AbstractAdminController;
use app\middleware\admin\AuthenticationMiddleware;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;

#[Group("/admin/api/merchant/bill")]
#[Middleware(AuthenticationMiddleware::class)]
class BillController extends AbstractAdminController
{
    #[Route("GET", "getBillList")]
    public function getBillList()
    {

    }
}