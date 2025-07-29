<?php

declare (strict_types=1);

namespace app\service\admin\impl;

use app\model\SystemDictionary;
use app\service\admin\DictionaryService;
use app\utils\TreeUtils;
use think\db\Query;
use think\facade\Db;

class DictionaryServiceImpl implements DictionaryService
{
    public function getDictionary(string $dictionaryName, string $keywords = '', string $where = ''): ?array
    {
        $dict = explode(",", $dictionaryName);

        $dictLength = count($dict);

        if ($dictLength == 1) {
            //普通字典查询
            $list = SystemDictionary::with(['dictionaryData' => function (Query $query) use ($keywords) {
                if ($keywords != '') {
                    $query->whereRaw("name like '%{$keywords}%'");
                }
                $query->where("status", 1)
                    ->field(['value as id', 'dictionary_id', 'name'])
                    ->select();
            }])->where("code", $dict[0])->find();

            return $list->dictionaryData->toArray();
        } elseif ($dictLength >= 3) {
            //远程表字典查询
            $prefix = env('DB_PREFIX');
            $table = explode('->', $dict[0]);

            try {
                $field = "{$dict[1]} as id,{$dict[2]} as name" . (array_key_exists(3, $dict) ? ",{$dict[3]} as parent_id" : '');
                $whereX = '';
                if ($keywords != '') {
                    $whereX .= " {$dict[2]} like '%{$keywords}%' and ";
                }

                if (count($table) == 2) {
                    $whereX .= "{$table[1]} and ";
                }

                if ($where != '') {
                    $whereX .= "{$where}";
                }

                if ($whereX != '') {
                    $whereX = 'where ' . $whereX;
                    $whereX = trim(trim(trim($whereX), 'and'));
                }

                $select = Db::query("select " . $field . " from " . $prefix . $table[0] . " " . $whereX . " order by id asc");

                if (array_key_exists(3, $dict)) {
                    $select = TreeUtils::generate((array)$select);
                }

            } catch (\Exception $e) {
                return null;
            }
            return $select;
        }

        return null;
    }
}