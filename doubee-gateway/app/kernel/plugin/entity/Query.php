<?php

declare (strict_types=1);

namespace app\kernel\plugin\entity;

class Query
{
    /**
     * 分页信息
     * @var array|null
     */
    public ?array $paginate = null;

    /**
     * 设置分页信息
     * @param int $page
     * @param int $limit
     * @return void
     */
    public function setPaginate(int $page = 1, int $limit = 15): void
    {
        $this->paginate = [$page, $limit];
    }
}