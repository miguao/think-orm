<?php

namespace app\controller\admin\api\merchant;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\MerchantPermission;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/admin/api/merchant/permission")]
#[Middleware(AuthenticationMiddleware::class)]
class PermissionController extends AbstractAdminController
{
    #[Route("GET", "getPermissionList")]
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