<?php

declare(strict_types=1);

namespace app\controller\admin\api\merchant;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Middleware;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\MerchantWithdraw;
use think\response\Json;

#[Controller("/admin/api/merchant/withdraw")]
#[Middleware(AuthenticationMiddleware::class)]
class WithdrawController extends AbstractAdminController
{
    #[GetMapping("getWithdrawList")]
    public function getWithdrawList(): Json
    {
        $map = $this->request->all();
        $get = new Get(MerchantWithdraw::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get);

        return $this->json(data: $data);
    }
}