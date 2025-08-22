<?php

declare(strict_types=1);

namespace app\controller\admin\api\logs;

use app\controller\AbstractAdminController;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\SystemBehaviorLog;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/admin/api/logs/behavior")]
#[Middleware(AuthenticationMiddleware::class)]
class BehaviorController extends AbstractAdminController
{
    #[Route("GET", "getLogList")]
    public function getLogList(): Json
    {
        $paginate = SystemBehaviorLog::query()
            ->page((int)$this->request->get('page'))
            ->paginate((int)$this->request->get('limit'))
            ->toArray();

        return $this->json(data: ["list" => $paginate['data'], "total" => $paginate['total']]);
    }
}