<?php

declare(strict_types=1);

namespace app\controller\admin\api\logs;

use app\controller\AbstractAdminController;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\SystemLoginLog;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/admin/api/logs/login")]
#[Middleware(AuthenticationMiddleware::class)]
class LoginController extends AbstractAdminController
{
    #[Route("GET", "getLogList")]
    public function getLogList(): Json
    {
        $paginate = SystemLoginLog::query()
            ->page((int)$this->request->get("page"))
            ->paginate((int)$this->request->get("limit"))
            ->toArray();

        return $this->json(data: ['list' => $paginate['data'], "total" => $paginate['total']]);
    }
}