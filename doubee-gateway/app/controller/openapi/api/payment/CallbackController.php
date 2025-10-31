<?php

declare(strict_types=1);

namespace app\controller\openapi\api\payment;

use app\controller\AbstractController;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use think\response\Json;

#[Controller("/openapi/api/payment/callback")]
class CallbackController extends AbstractController
{
    #[GetMapping("GET", "callback")]
    public function callback(): Json
    {
        return $this->json();
    }
}