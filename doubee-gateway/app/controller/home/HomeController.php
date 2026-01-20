<?php

declare(strict_types=1);

namespace app\controller\home;

use app\controller\AbstractController;
use app\kernel\plugin\PluginFactory;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Inject;
use app\model\PaymentChannel;
use app\model\PaymentOrder;
use app\service\admin\GeeTestService;
use app\utils\StringUtils;

class HomeController extends AbstractController
{
    #[Inject]
    protected GeeTestService $geeTestService;

    #[GetMapping("/")]
    public function index()
    {
        $params = [
            'merchant_no' => '3877619169537453',
            'application_no' => '2026010387762338',
            'out_trade_no' => '2026010822143525866',
            'subject' => '余额充值',
            'amount' => '1.00',
            'payment_type' => 'ALIPAY',
            'notification_url' => 'https://cccyun.nanoa.cn/pay/notify/2026010822143525866/',
            'redirect_url' => '',
            'payer_ip' => '182.133.233.108',
        ];
        $params['sign'] = StringUtils::generateSignature($params, "8d7bc1e18d3275ed1efd8be81974385e");
        $params['sign_type'] = 'MD5';
        print_r($params);
        exit;

//        $get = $this->request->get();
//        print_r($this->geeTestService->getBehavioralVerificationCode($get['lot_number'], $get['captcha_output'], $get['pass_token'], $get['gen_time']));
        return $this->json(message: "hello,word");
    }

    #[GetMapping("trade")]
    public function trade()
    {
        $paymentOrder = new PaymentOrder();
        $paymentOrder->trade_no = StringUtils::generateTradeNo();
        $paymentOrder->subject = "在线充值，订单号：" . StringUtils::generateTradeNo();

        $config = PaymentChannel::query()->where("plugin_identifier", "AliPay")->find()->config;

        $class = PluginFactory::getInstance()->getPaymentHandler(
            identifier: "Alipay",
            paymentOrder: $paymentOrder,
            config: $config,
            clientIp: "127.0.0.1",
            amount: 0.01,
            notificationUrl: "https://testing.nanoa.cn/",
            redirectUrl: null
        );

        $entity = $class->create();
        print_r($entity->getPayUrl());
        exit;
    }
}