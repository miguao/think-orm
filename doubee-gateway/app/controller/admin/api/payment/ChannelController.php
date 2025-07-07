<?php

declare(strict_types=1);

namespace app\controller\admin\api\payment;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\PaymentChannel;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\db\Query;
use think\response\Json;

#[Group("/admin/api/payment/channel")]
#[Middleware(AuthenticationMiddleware::class)]
class ChannelController extends AbstractAdminController
{
    #[Route("GET", "getChannelList")]
    public function getChannelList(): Json
    {
        $map = $this->request->all();
        $get = new Get(PaymentChannel::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get, function (Query $query) {
            return $query->with(['bank']);
        });

        return $this->json(data: $data);
    }
}