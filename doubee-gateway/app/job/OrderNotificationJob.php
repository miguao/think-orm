<?php

declare(strict_types=1);

namespace app\job;

use app\kernel\queue\contract\JobInterface;
use app\model\PaymentOrder;
use app\utils\StringUtils;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
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
        $attempts = $job->attempts();
        if ($attempts > $this->maxAttempts) {
            $job->delete();
            return;
        }

        $order = PaymentOrder::query()
            ->with(['application', 'type', 'merchant'])
            ->find($data['id']);

        if (!$order || empty($order->notification_url)) {
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

        try {
            $client = new Client(['verify' => false, 'timeout' => 10, 'http_errors' => false]);
            $response = $client->post($data['notification_url'], [RequestOptions::FORM_PARAMS => $params]);
            $body = trim((string)$response->getBody());
            if ($body === 'success') {
                $order->notification_status = 1;
                $order->save();

                $job->delete();
                return;
            }

            throw new \RuntimeException('Notify response invalid: ' . $body);
        } catch (Throwable $throwable) {
            // 未成功，延迟重试
            $delay = $this->retryDelay($attempts);

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