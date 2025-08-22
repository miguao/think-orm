<?php

declare(strict_types=1);

namespace app\controller\admin\api\merchant;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\MerchantIdentity;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\db\Query;
use think\response\Json;

#[Group("/admin/api/merchant/identity")]
#[Middleware(AuthenticationMiddleware::class)]
class IdentityController extends AbstractAdminController
{
    #[Route("GET", "getIdentityList")]
    public function getIdentityList(): Json
    {
        $map = $this->request->get();
        $get = new Get(MerchantIdentity::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get, function (Query $query) {
            return $query->with(['merchant']);
        });

        return $this->json(data: $data);
    }
}