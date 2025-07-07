<?php

declare(strict_types=1);

namespace app\controller\admin\api\payment;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\PaymentOrder;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\db\Query;
use think\model\Relation;
use think\response\Json;

#[Group("/admin/api/payment/order")]
#[Middleware(AuthenticationMiddleware::class)]
class OrderController extends AbstractAdminController
{
    #[Route("GET", "getOrderList")]
    public function getOrderList(): Json
    {
        $map = $this->request->all();
        $get = new Get(PaymentOrder::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get, function (Query $builder) {
            return $builder->with(['merchant', 'application', 'channel', 'bank']);
        });

        return $this->json(data: $data);
    }
}