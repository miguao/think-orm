<?php

declare(strict_types=1);

namespace app\job;

use app\kernel\queue\contract\JobInterface;
use app\model\PaymentOrder;
use app\utils\StringUtils;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use think\facade\Log;
use think\queue\Job;
use Throwable;

class OrderNotificationJob implements JobInterface
{
    /**
     * 最大重试次数
     * @var int
     */
    protected int $maxAttempts = 5;

    public function fire(Job $job, array $data): void
    {
        Log::info("进入队列第一步");

        $attempts = $job->attempts();
        if ($attempts > $this->maxAttempts) {
            Log::info("进入队列第二步，超过最大重试次数");

            $job->delete();
            return;
        }

        Log::info("进入队列第三步，查询订单信息");
        $order = PaymentOrder::query()
            ->with(['application', 'type', 'merchant'])
            ->find($data['id']);

        if (!$order || empty($order->notification_url)) {
            Log::info("进入队列第四步，数据异常，直接结束");

            // 数据异常，直接结束
            $job->delete();
            return;
        }

        $params = [
            'merchant_no' => $order->merchant_no,
            'application_no' => $order->application->application_no,
            'out_trade_no' => $order->out_trade_no,
            'subject' => $order->subject,
            'amount' => $order->amount,
            'payment_type' => $order->type,
            'notification_url' => $order->notification_url,
            'redirect_url' => $order->redirect_url,
            'payer_ip' => $order->payer_ip,
        ];
        $params['sign'] = StringUtils::generateSignature($params, $order->merchant->merchant_key);
        $params['sign_type'] = 'MD5';

        Log::info("进入队列第五步，拼接验签参数");

        try {
            $client = new Client(['verify' => false, 'timeout' => 10, 'http_errors' => false]);

            Log::info("进入队列第六步，开始发送请求");

            $response = $client->post($data['notification_url'], [RequestOptions::FORM_PARAMS => $params]);
            $body = trim((string)$response->getBody());

            Log::info("进入队列第七步，获取响应数据");

            if ($body === 'success') {
                Log::info("进入队列第八步，更新订单状态");

                $order->notification_status = 1;
                $order->save();

                $job->delete();

                Log::info("进入队列第九步，结束通知");
                return;
            }

            throw new \RuntimeException('Notify response invalid: ' . $body);
        } catch (Throwable $throwable) {
            // 未成功，延迟重试
            $delay = $this->retryDelay($attempts);

            Log::info("进入队列第十步，延迟重试");

            $job->release($delay);
        }
    }

    public function failed($data): void
    {
        // TODO: Implement failed() method.
    }

    /**
     * 通知重试延迟策略（秒）
     * @param int $attempts
     * @return int
     */
    protected function retryDelay(int $attempts): int
    {
        return match ($attempts) {
            1 => 5,
            2 => 15,
            3 => 30,
            4 => 60,
            default => 120,
        };
    }
}