<?php

namespace app\controller\admin\api\merchant;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Middleware;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\MerchantPermission;
use think\response\Json;

#[Controller("/admin/api/merchant/permission")]
#[Middleware(AuthenticationMiddleware::class)]
class PermissionController extends AbstractAdminController
{
    #[GetMapping("getPermissionList")]
    public function getPermissionList(): Json
    {
        $map = $this->request->get();
        $get = new Get(MerchantPermission::class);
        $get->setWhere((array)$map);
        $get->setOrderBy("sort", "desc");
        $data = $this->database->get($get);

        return $this->json(data: $data);
    }
}