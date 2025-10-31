<?php

declare(strict_types=1);

namespace app\service\openapi\impl;

use app\exception\JsonException;
use app\model\Merchant;
use app\model\MerchantApplication;
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

        $application = MerchantApplication::query()
            ->where("merchant_id", $merchant->id)
            ->where("application_no", $map['application_no'])
            ->find();
        if (!$application) {
            throw new JsonException("应用不存在");
        }

        $channel = PaymentChannel::query()->find($map['channel_id']);
        if (!$channel) {
            throw new JsonException("通道不存在");
        }
        if ($channel->status != 1) {
            throw new JsonException("当前通道暂时关闭，如有异常请联系客服。");
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

            $plugin = new Payment();
            $plugin->order = $paymentOrder;
            $plugin->business = [
                'app_id' => '2021004156668150',
                'apply_private_key' => 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCBEba23Aqk4dYuEwR0mSAFMo3oyQKQUPjzE1eWq6kqJ3F/jc/KCXPl2pXKXnt2zfwRT3q9pzVSrMaAkkFGmE34hYqf/yOwK4d3rqVfHUR3T//v8ExouhLX5P7VYfAXx7Un2ndb92o2CGtvtBiC8jsQxmgRL5l6t+QHwnRq18Gsy0xZFbdeszYq+klmWc33wsd7V3CZDLEWgjbLRwF4Se0XxaGyst/rYBPqi4WBcr1axM8e2OTtQTchnTeqT8A55cWsYI+wmMJKNamSb8lzd3mvM2VL+JYT3gfQOUwGlhcvZJ4gjKp5s/qb8HWRv/eRTp/HTqPzcxFBiV0Unfp2sPyPAgMBAAECggEAMmy9OYuWDPy+NJnxb6wzOlSZ5IxDmCZKXIz99ztxh6qJSRAaUhCHWxGe28U17yBijlDC3z1oM7iUhL6fCfmO3L0vf3qqcrVO448zxIkmqoZys0bqUy//FKG+fZcZufCn8b4ES8XJlPqNHsL5oI8KTG93UN+L0sntKKrr2KIaZGsp4xCnmjB1ErzgvqyKdC2KayZ53izPPi2bSGvX3E2dhVJB4bfTf45rQn+YTQTxI5OoZXi4MvhVXZpZ6qhFybMJdkHPuReVRCIRPs34vg9wKWDa0Vwc2Xwsjn/iz4t7ElWh1lZ3RwDxcWm2MQofAAPfSJ9iWl5Z6Ef7oG2cUi3b4QKBgQDILuCy37a8iPT2BJ06L4K774k/kahdhB1osSzbt2kXUMy5CyWTgy+o81tSwjW2mV5seRF187P8vuwiSDOakdsnUGLSVAatMdrBj39BWHM9AgCjIo5soJgOdXFM4Iy5raIMQFI2reOuDv2kw7At9gpTnjffBTaUnIp9HNCPfep5zQKBgQClDrS7HnsQDrRNtQffzZwBOIeNaG4SHLlB3RSV5PpVNTy/BXCw1mSKoacxD6dEqR2jEPN9amihY9q4yaHDAxlYPMNotK8N5itrgJFZ31gU6iN6lz+L5iAKIBDkZgDwvudISaYZb4GBwjqHRNcJNxf9zsvfB2zgZxD+MvsVTqkDywKBgEm4ccmN95bw/P1I77oI2yVo7mt5sb5WtBnk/bcgkVQjznDq+PidHEUk8eHFYdCQJKTf5R1CFWujyARMAK0Qv9t3kB0TEq5fqMcVmHCxKSczgJaTWE4KylSwzXvZShnrJr1KRryaRznHjYNLCauza1Ql9T9thQ8voxS2MtxVK6+VAoGAF1Ts10aHvs1VT2a3pjs6QjHrd2G7rXPB/D10G3wyprjPzXDHbTfhHnebWqQL0PjW56YNxTy1bZrf4B8gbqCRhJeSR8rCtARFPvz9juazXMF8Imge43e0L7o4qRCCMYXKy70dbZsFg4IhI93nR7Yx/f6tculpyo7HKCestmZYHeECgYEAikzLQAYJ2ICNQvm42jcQpyKn9SCHkQl02X3b1wTETCeAm47rsS0mJfFlMymc98hfMYGmJzxkwBudXDJ9XgAT0V3BaH0We1lSWk2Hj2XLsVc8ttw4WCWSHQKPAj6fh8OAo5arZfTSkrhQPXjrAJywhvgio89qWk6ZzqC7cuuIwNU=',
                'alipay_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4QtauTRo29kR+L26l18VQfPbyEdnZ1GZGMoQbeoneaWiGH+0Fhzq86RZxKJ9f8q221oIJ1hpZV+6SndqRGLFOGuA12njaPo7eQSiME55fV2MDLtzRyD8/ZSi8WSb/uiOIanagOLcLC+/NEO6wqLoSbiaVkPGh4DUPLB7R6biE8olBiUNlgYzm+mPOtiabtqE3bz7BBcyli6d0FUmLTD1Jk09oQQNW3QvDPWlS+Fd56zqCj2Hlx/8vE/E/P9/Z2+iG7cZ5pmd7Sm/lmZ5z9XdWuj549DgtTIhQ7gHihgrRbxMmocLTHOSlWbhPD+eyrT6G2iONjnFhUewLhwG7lWMZwIDAQAB'
            ];
            return $plugin->create();
        });


        return ['url' => $order];
    }
}