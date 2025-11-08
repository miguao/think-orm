<?php

declare(strict_types=1);

namespace app\controller\home;

use app\controller\AbstractController;
use app\kernel\plugin\PaymentFactory;
use app\kernel\plugin\PluginFactory;
use app\kernel\route\annotation\GetMapping;
use app\model\PaymentChannel;
use app\model\PaymentOrder;
use app\utils\StringUtils;
use think\response\Json;

class HomeController extends AbstractController
{
    #[GetMapping("/")]
    public function index(): Json
    {
        return $this->json(message: "hello,word");
    }

    #[GetMapping("trade")]
    public function trade()
    {
        $paymentOrder = new PaymentOrder();
        $paymentOrder->trade_no = StringUtils::generateTradeNo();
        $paymentOrder->subject = "在线充值，订单号：" . StringUtils::generateTradeNo();

        $config = PaymentChannel::query()->where("plugin_identifier", "AliPay")->find()->config;

        $class = PaymentFactory::getInstance()->getHandler(
            identifier: "Alipay",
            paymentOrder: $paymentOrder,
            config: $config,
            clientIp: "127.0.0.1",
            amount: 0.01,
            notificationUrl: "https://testing.nanoa.cn/",
            redirectUrl: null
        );
        print_r($class->create());
        exit;
    }
}