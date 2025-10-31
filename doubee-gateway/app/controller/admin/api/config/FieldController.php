<?php

declare(strict_types=1);

namespace app\controller\admin\api\config;

use app\controller\AbstractAdminController;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Middleware;
use app\middleware\admin\AuthenticationMiddleware;

#[Controller("/admin/api/config/field")]
#[Middleware(AuthenticationMiddleware::class)]
class FieldController extends AbstractAdminController
{
    #[GetMapping("getFieldList")]
    public function getFieldList()
    {

    }
}