<?php

declare(strict_types=1);

namespace app\controller\admin\api\dashboard;

use app\controller\AbstractAdminController;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Middleware;
use app\middleware\admin\AuthenticationMiddleware;
use think\response\Json;

#[Controller("/admin/api/dashboard/console")]
#[Middleware(AuthenticationMiddleware::class)]
class ConsoleController extends AbstractAdminController
{
    #[GetMapping("getOverviewData")]
    public function getOverviewData(): Json
    {
        return $this->json();
    }
}