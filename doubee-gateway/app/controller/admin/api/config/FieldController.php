<?php

declare(strict_types=1);

namespace app\controller\admin\api\config;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Middleware;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\SystemConfigField;
use think\response\Json;

#[Controller("/admin/api/config/field")]
#[Middleware(AuthenticationMiddleware::class)]
class FieldController extends AbstractAdminController
{
    #[GetMapping("getFieldList")]
    public function getFieldList(): Json
    {
        $map = $this->request->get();
        $get = new Get(SystemConfigField::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get);

        return $this->json(data: $data);
    }
}