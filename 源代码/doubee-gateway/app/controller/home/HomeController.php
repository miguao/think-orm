<?php

declare(strict_types=1);

namespace app\controller\home;

use app\controller\AbstractController;
use think\response\Json;

class HomeController extends AbstractController
{
    public function index(): Json
    {
        return $this->json(message: "hello,word");
    }
}