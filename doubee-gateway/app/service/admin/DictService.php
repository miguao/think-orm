<?php

declare (strict_types=1);

namespace app\service\admin;

interface DictService
{
    /**
     * 获取字典
     * @param string $dictName
     * @param string $keywords
     * @param string $where
     * @return array|null
     */
    public function getDict(string $dictName, string $keywords = '', string $where = ''): ?array;
}