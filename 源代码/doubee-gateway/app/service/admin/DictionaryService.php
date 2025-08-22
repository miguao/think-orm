<?php

declare (strict_types=1);

namespace app\service\admin;

interface DictionaryService
{
    /**
     * 获取字典
     * @param string $dictionaryName
     * @param string $keywords
     * @param string $where
     * @return array|null
     */
    public function getDictionary(string $dictionaryName, string $keywords = '', string $where = ''): ?array;
}