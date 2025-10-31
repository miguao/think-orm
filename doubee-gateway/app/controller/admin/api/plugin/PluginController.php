<?php

declare (strict_types=1);

namespace app\controller\admin\api\plugin;

use app\controller\AbstractAdminController;
use app\kernel\plugin\entity\Query;
use app\kernel\plugin\PluginFactory;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Middleware;
use app\middleware\admin\AuthenticationMiddleware;
use think\response\Json;

#[Controller("/admin/api/plugin")]
#[Middleware(AuthenticationMiddleware::class)]
class PluginController extends AbstractAdminController
{
    #[GetMapping("getInstalledPlugins")]
    public function getInstalledPlugins(): Json
    {
        $query = new Query();
        $query->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = PluginFactory::getInstance()->getInstalledPlugins($query);
        return $this->json(data: $data);
    }

    #[GetMapping("previewIcon")]
    public function previewIcon()
    {

    }
}