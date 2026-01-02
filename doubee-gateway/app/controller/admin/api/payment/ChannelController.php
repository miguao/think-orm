<?php

declare(strict_types=1);

namespace app\controller\admin\api\payment;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\entity\database\Save;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Middleware;
use app\kernel\route\annotation\RequestMapping;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\PaymentChannel;
use think\db\Query;
use think\response\Json;

#[Controller("/admin/api/payment/channel")]
#[Middleware(AuthenticationMiddleware::class)]
class ChannelController extends AbstractAdminController
{
    #[GetMapping("getChannelList")]
    public function getChannelList(): Json
    {
        $map = $this->request->all();
        $get = new Get(PaymentChannel::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get, function (Query $query) {
            return $query->with(['type']);
        });

        return $this->json(data: $data);
    }

    #[RequestMapping("saveChannel", ["POST", "PUT"])]
    public function saveChannel(): Json
    {
        $map = $this->request->post();
        $save = new Save(PaymentChannel::class);
        $save->setMap($map);
        $save->enableCreateTime();

        $this->database->save($save);
        return $this->json(message: "保存成功");
    }
}