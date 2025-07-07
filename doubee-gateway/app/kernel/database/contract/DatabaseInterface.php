<?php

declare(strict_types=1);

namespace app\kernel\database\contract;

use app\entity\database\Delete;
use app\entity\database\Get;
use app\entity\database\Save;

interface DatabaseInterface
{
    const RESULT_TYPE_ARRAY = 0;
    const RESULT_TYPE_RAW = 4;

    /**
     * 获取数据
     * @param Get $get
     * @param callable|null $append
     * @param int $resultType
     * @return mixed
     */
    public function get(Get $get, ?callable $append = null, int $resultType = self::RESULT_TYPE_ARRAY): mixed;

    /**
     * 保存数据
     * @param Save $save
     * @return mixed
     */
    public function save(Save $save): mixed;

    /**
     * 删除数据
     * @param Delete $delete
     * @return int
     */
    public function delete(Delete $delete): int;

    /**
     * 获取排序
     * @param array $map
     * @param string $field
     * @param string $rule
     * @return array
     */
    public function getOrderBy(array $map, string $field, string $rule = 'desc'): array;
}