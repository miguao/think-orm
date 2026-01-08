<?php

declare(strict_types=1);

namespace app\kernel\queue\contract;

use think\queue\Job;

interface JobInterface
{
    /**
     * 队列任务执行
     * @param Job $job
     * @param array $data
     * @return void
     */
    public function fire(Job $job, array $data): void;

    /**
     * 队列任务失败回调
     * @param $data
     * @return void
     */
    public function failed($data): void;
}