<?php

declare(strict_types=1);

namespace app\controller\openapi\api\payment;

use app\controller\AbstractController;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\Inject;
use app\kernel\route\annotation\PostMapping;
use app\kernel\route\annotation\RequestMapping;
use app\service\openapi\OrderService;
use think\response\Json;

#[Controller("/openapi/api/payment/order")]
class OrderController extends AbstractController
{
    #[Inject]
    protected OrderService $orderService;

    #[PostMapping("trade")]
    public function trade(): Json
    {
        $map = $this->request->post();
        $this->validator((array)$map, [
            'merchant_no' => 'require',
            'application_no' => 'require',
            'channel_id' => 'require',
            'out_trade_no' => 'require',
            'subject' => 'require',
            'amount' => 'require',
            'notification_url' => 'require',
            'sign' => 'require',
        ], [
            'merchant_no.require' => '商户号不能为空',
            'application_no.require' => '应用号不能为空',
            'channel_id.require' => '通道ID不能为空',
            'out_trade_no.require' => '商户订单号不能为空',
            'subject.require' => '商品名称不能为空',
            'amount.require' => '订单金额不能为空',
            'notification_url.require' => '回调地址不能为空',
            'sign.require' => '签名值不能为空',
        ]);

        $trade = $this->orderService->trade($map);
        return $this->json(message: "下单成功", data: $trade);
    }

    #[RequestMapping("callback")]
    public function callback(): Json
    {
        return $this->json();
    }
}