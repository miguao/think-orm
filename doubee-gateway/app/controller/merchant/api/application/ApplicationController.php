<?php

declare(strict_types=1);

namespace app\controller\merchant\api\application;

use app\controller\AbstractMerchantController;
use app\entity\database\Get;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use app\model\MerchantApplication;
use think\response\Json;

#[Controller("/merchant/api/application")]
class ApplicationController extends AbstractMerchantController
{
    #[GetMapping("getApplicationList")]
    public function getApplicationList(): Json
    {
        $map = $this->request->all();
        $get = new Get(MerchantApplication::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get);

        return $this->json(data: $data);
    }
}