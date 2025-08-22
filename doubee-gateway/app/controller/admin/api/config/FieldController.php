<?php

declare(strict_types=1);

namespace app\controller\admin\api\config;

use app\controller\AbstractAdminController;
use app\middleware\admin\AuthenticationMiddleware;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;

#[Group("/admin/api/config/field")]
#[Middleware(AuthenticationMiddleware::class)]
class FieldController extends AbstractAdminController
{
    #[Route("GET", "getFieldList")]
    public function getFieldList()
    {

    }
}