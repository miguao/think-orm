<?php

declare(strict_types=1);

namespace app\controller\admin\api\payment;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Middleware;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\PaymentOrder;
use think\db\Query;
use think\response\Json;

#[Controller("/admin/api/payment/order")]
#[Middleware(AuthenticationMiddleware::class)]
class OrderController extends AbstractAdminController
{
    #[GetMapping("getOrderList")]
    public function getOrderList(): Json
    {
        $map = $this->request->all();
        $get = new Get(PaymentOrder::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get, function (Query $builder) {
            return $builder->with(['merchant', 'application', 'channel', 'type']);
        });

        return $this->json(data: $data);
    }
}