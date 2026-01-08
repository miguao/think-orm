<?php

declare(strict_types=1);

namespace app\job;

use app\kernel\queue\contract\JobInterface;
use think\queue\Job;

class OrderNotificationJob implements JobInterface
{
    /**
     * 最大重试次数
     * @var int
     */
    protected int $maxAttempts = 5;

    public function fire(Job $job, array $data): void
    {
        // TODO: Implement fire() method.
    }

    public function failed($data): void
    {
        // TODO: Implement failed() method.
    }
}