<?php

declare(strict_types=1);

namespace app\controller\openapi\api\payment;

use app\controller\AbstractController;
use think\annotation\route\Group;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/openapi/api/payment/callback")]
class CallbackController extends AbstractController
{
    #[Route("GET", "callback")]
    public function callback(): Json
    {
        return $this->json();
    }
}