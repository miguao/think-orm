<?php

declare(strict_types=1);

namespace app\controller\home;

use app\controller\AbstractController;
use app\kernel\route\annotation\GetMapping;
use think\response\Json;

class HomeController extends AbstractController
{
    #[GetMapping("/")]
    public function index(): Json
    {
        return $this->json(message: "hello,word");
    }
}