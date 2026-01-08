<?php

declare(strict_types=1);

namespace app\service\openapi;

use app\exception\JsonException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

interface OrderService
{
    /**
     * 统一下单
     * @param array $map
     * @return array
     * @throws JsonException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function trade(array $map): array;

    public function callback(array $map);
}