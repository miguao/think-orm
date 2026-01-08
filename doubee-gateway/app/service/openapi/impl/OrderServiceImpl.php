<?php

declare(strict_types=1);

namespace app\service\openapi\impl;

use app\exception\JsonException;
use app\kernel\plugin\PluginFactory;
use app\model\Merchant;
use app\model\MerchantApplication;
use app\model\PaymentType;
use app\model\PaymentChannel;
use app\model\PaymentOrder;
use app\service\openapi\OrderService;
use app\utils\DateUtils;
use app\utils\StringUtils;
use think\facade\Db;
use think\Response;

class OrderServiceImpl implements OrderService
{
    public function trade(array $map): array
    {
        $merchant = Merchant::query()->where("merchant_no", $map['merchant_no'])->find();
        if (!$merchant) {
            throw new JsonException("商户号不存在");
        }
        if ($merchant->status != 1) {
            throw new JsonException("当前商户状态异常，如有疑问请联系客服。");
        }

        // 查询商户应用
        $application = MerchantApplication::query()
            ->where("merchant_id", $merchant->id)
            ->where("application_no", $map['application_no'])
            ->find();
        if (!$application) {
            throw new JsonException("应用不存在");
        }

        // 查询支付类型
        $paymentType = PaymentType::query()->where("code", $map['payment_type'])->find();
        if (!$paymentType) {
            throw new JsonException("支付类型不存在");
        }

        // 查询可用支付通道
        $channel = PaymentChannel::query()->where("type_id", $paymentType->id)->find();
        if (!$channel) {
            throw new JsonException("无可用通道，请尝试其他支付方式。");
        }

        $amount = (float)$map['amount'];
        if ($amount <= 0) {
            throw new JsonException("下单金额不能为0或低于0");
        }

        $signature = StringUtils::generateSignature($map, $merchant->merchant_key);
        if ($map['sign'] != $signature) {
            throw new JsonException("签名错误");
        }

        return Db::transaction(function () use ($map, $merchant, $application, $channel, $amount) {
            $merchantId = (int)$merchant->id;
            $applicationId = (int)$application->id;
            $channelId = (int)$channel->id;
            $typeId = (int)$channel->typeId;

            $tradeNo = StringUtils::generateTradeNo();
            $outTradeNo = $map['out_trade_no'];
            $payerIp = $map['payer_ip'] ?? null;
            $redirectUrl = $map['redirect_url'] ?? null;

            $paymentOrder = new PaymentOrder();
            $paymentOrder->merchant_id = $merchantId;
            $paymentOrder->application_id = $applicationId;
            $paymentOrder->channel_id = $channelId;
            $paymentOrder->type_id = $typeId;
            $paymentOrder->trade_no = $tradeNo;
            $paymentOrder->out_trade_no = $outTradeNo;
            $paymentOrder->subject = $map['subject'];
            $paymentOrder->amount = $amount;
            $paymentOrder->actual_amount = $amount;
            $paymentOrder->payer_ip = $payerIp;
            $paymentOrder->creation_time = DateUtils::current();
            $paymentOrder->status = 0;
            $paymentOrder->save();

            $handler = PluginFactory::getInstance()->getPaymentHandler(
                $channel->plugin_identifier,
                $paymentOrder,
                (array)$channel->config,
                $payerIp,
                $amount,
                $map['notification_url'],
                $redirectUrl
            );

            $pay = $handler->create();
            return ['url' => $pay->getPayUrl()];
        });
    }

    public function query(?string $tradeNo = null, ?string $outTradeNo = null): array
    {
        $query = PaymentOrder::query()
            ->when(!empty($tradeNo),
                fn($query) => $query->where('trade_no', $tradeNo),
                fn($query) => $query->where('out_trade_no', $outTradeNo)
            );

        $order = $query->find();
        if (!$order) {
            throw new JsonException('订单不存在');
        }

        return $order->toArray();
    }

    public function callback(string $tradeNo, array $map): Response
    {
        $paymentOrder = PaymentOrder::newQuery()->where('trade_no', $tradeNo)->find();
        if (!$paymentOrder) {
            throw new JsonException("订单不存在或已失效");
        }

        $channel = PaymentChannel::query()->find($paymentOrder->channel_id);
        if (!$channel) {
            throw new JsonException("渠道信息不存在");
        }

        $handler = PluginFactory::getInstance()->getPaymentHandler(
            $channel->plugin_identifier,
            $paymentOrder,
            (array)$channel->config,
        );

        return $handler->async();
    }
}