<?php

declare(strict_types=1);

namespace app\service\openapi\impl;

use app\exception\JsonException;
use app\kernel\plugin\PaymentFactory;
use app\model\Merchant;
use app\model\MerchantApplication;
use app\model\PaymentBank;
use app\model\PaymentChannel;
use app\model\PaymentOrder;
use app\plugin\AliPay\Handler\Payment;
use app\service\openapi\OrderService;
use app\utils\DateUtils;
use app\utils\StringUtils;
use think\facade\Db;

class OrderServiceImpl implements OrderService
{
    public function trade(array $map): array
    {
        $merchant = Merchant::query()->where("merchant_no", $map['merchant_no'])->find();
        if (!$merchant) {
            throw new JsonException("商户号不存在");
        }
        if ($merchant->status != 1) {
            throw new JsonException("当前商户状态异常，如有异常请联系客服。");
        }

        // 查询商户应用
        $application = MerchantApplication::query()
            ->where("merchant_id", $merchant->id)
            ->where("application_no", $map['application_no'])
            ->find();
        if (!$application) {
            throw new JsonException("应用不存在");
        }

        // 根据银行代码查找对应银行信息
        $bank = PaymentBank::query()->where("code", $map['bank_code'])->find();
        if (!$bank) {
            throw new JsonException("银行代码不存在");
        }

        // 根据银行ID查找可用支付通道
        $channel = PaymentChannel::query()->where("bank_id", $bank->id)->find();
        if (!$channel) {
            throw new JsonException("无可用通道，请尝试其他支付方式。");
        }

        $amount = (float)$map['amount'];
        if ($amount <= 0) {
            throw new JsonException("下单金额不能为0或低于0");
        }

        $signature = StringUtils::generateSignature($map, $application->secret);
        if ($map['sign'] != $signature) {
            throw new JsonException("签名错误");
        }

        $order = Db::transaction(function () use ($map, $merchant, $application, $channel, $amount) {
            $merchantId = (int)$merchant->id;
            $applicationId = (int)$application->id;
            $channelId = (int)$channel->id;
            $banKId = (int)$channel->bank_id;

            $tradeNo = StringUtils::generateTradeNo();
            $outTradeNo = $map['out_trade_no'];

            $paymentOrder = new PaymentOrder();
            $paymentOrder->merchant_id = $merchantId;
            $paymentOrder->application_id = $applicationId;
            $paymentOrder->channel_id = $channelId;
            $paymentOrder->bank_id = $banKId;
            $paymentOrder->trade_no = $tradeNo;
            $paymentOrder->out_trade_no = $outTradeNo;
            $paymentOrder->subject = $map['subject'];
            $paymentOrder->amount = $amount;
            $paymentOrder->actual_amount = $amount;
            $paymentOrder->payer_ip = request()->ip();
            $paymentOrder->creation_time = DateUtils::current();
            $paymentOrder->status = 0;
            $paymentOrder->save();

            $plugin = PaymentFactory::getInstance()->getHandler($channel->plugin_identifier, (array)$channel->config);
            return $plugin->create();
        });


        return ['url' => $order];
    }
}