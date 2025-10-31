<?php

declare(strict_types=1);

namespace app\controller\admin\api\merchant;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Middleware;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\MerchantBankCard;
use think\db\Query;
use think\response\Json;

#[Controller("/admin/api/merchant/bank/card")]
#[Middleware(AuthenticationMiddleware::class)]
class BankCardController extends AbstractAdminController
{
    #[GetMapping("getCardList")]
    public function getCardList(): Json
    {
        $map = $this->request->all();
        $get = new Get(MerchantBankCard::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get, function (Query $query) {
            return $query->with(['bank', 'merchant']);
        });

        return $this->json(data: $data);
    }
}