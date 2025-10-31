<?php

declare (strict_types=1);

namespace app\controller\admin\api\plugin;

use app\controller\AbstractAdminController;
use app\kernel\plugin\entity\Query;
use app\kernel\plugin\PluginFactory;
use app\middleware\admin\AuthenticationMiddleware;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/admin/api/plugin")]
#[Middleware(AuthenticationMiddleware::class)]
class PluginController extends AbstractAdminController
{
    #[Route("GET", "getInstalledPlugins")]
    public function getInstalledPlugins(): Json
    {
        $query = new Query();
        $query->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = PluginFactory::getInstance()->getInstalledPlugins($query);
        return $this->json(data: $data);
    }

    public function previewIcon()
    {

    }
}