<?php

declare(strict_types=1);

namespace app\controller\admin\api\dashboard;

use app\controller\AbstractAdminController;
use app\middleware\admin\AuthenticationMiddleware;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/admin/api/dashboard/console")]
#[Middleware(AuthenticationMiddleware::class)]
class ConsoleController extends AbstractAdminController
{
    #[Route("GET", "getOverviewData")]
    public function getOverviewData(): Json
    {
        return $this->json();
    }
}