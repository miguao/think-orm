<?php

declare(strict_types=1);

namespace app\controller\admin\api\merchant;

use app\controller\AbstractAdminController;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Middleware;
use app\middleware\admin\AuthenticationMiddleware;

#[Controller("/admin/api/merchant/bill")]
#[Middleware(AuthenticationMiddleware::class)]
class BillController extends AbstractAdminController
{
    #[GetMapping("getBillList")]
    public function getBillList()
    {

    }
}