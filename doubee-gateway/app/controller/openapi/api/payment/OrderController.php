<?php

declare(strict_types=1);

namespace app\controller\openapi\api\payment;

use app\controller\AbstractController;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Inject;
use app\kernel\route\annotation\PostMapping;
use app\kernel\route\annotation\RequestMapping;
use app\service\openapi\OrderService;
use think\Response;
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
            'out_trade_no' => 'require',
            'subject' => 'require',
            'amount' => 'require',
            'integration_mode' => 'require',
            'payment_type' => 'require',
            'notification_url' => 'require',
            'sign' => 'require',
            'sign_type' => 'require',
        ], [
            'merchant_no.require' => '商户号不能为空',
            'application_no.require' => '应用号不能为空',
            'out_trade_no.require' => '商户订单号不能为空',
            'subject.require' => '商品名称不能为空',
            'amount.require' => '订单金额不能为空',
            'integration_mode.require' => '接入模式不能为空',
            'payment_type.require' => '支付类型不能为空',
            'notification_url.require' => '通知地址不能为空',
            'sign.require' => '签名值不能为空',
            'sign_type.require' => '签名类型不能为空',
        ]);

        $trade = $this->orderService->trade($map);
        return $this->json(message: "下单成功", data: $trade);
    }

    #[GetMapping("query")]
    public function query(): Json
    {
        $map = $this->request->get();
        $this->validator((array)$map, ['trade_no' => 'requireWithout:out_trade_no']);

        $data = $this->orderService->query($this->request->get('trade_no'), $this->request->get('out_trade_no'));
        return $this->json(data: $data);
    }

    #[RequestMapping("callback/:trade_no")]
    public function callback(): Response
    {
        $map = $this->request->all();
        $tradeNo = $this->request->route('trade_no');
        return $this->orderService->callback($tradeNo, $map);
    }
}