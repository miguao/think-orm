<?php

declare (strict_types=1);

namespace app\kernel\plugin\entity;

class Query
{
    /**
     * 插件类型
     * @var string|null
     */
    public ?string $type = null;

    /**
     * 分页信息
     * @var array|null
     */
    public ?array $paginate = null;

    /**
     * 设置插件类型
     * @param string|null $type
     * @return void
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

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