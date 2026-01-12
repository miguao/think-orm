<?php

declare(strict_types=1);

namespace app\kernel\database;

use app\entity\database\Delete;
use app\entity\database\Get;
use app\entity\database\Save;
use app\exception\JsonException;
use app\kernel\database\contract\DatabaseInterface;
use app\utils\DateUtils;
use think\Container;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;

class Database implements DatabaseInterface
{
    private function getTable(string $model): string
    {
        $instance = Container::getInstance()->make($model);
        return $instance->getTable();
    }

    private function setWhere(mixed &$query, string $type, string $column, string $val): void
    {
        switch ($type) {
            case "equal":
                $query = $query->where($column, $val);
                break;
            case "betweenStart":
                $query = $query->where($column, ">=", $val);
                break;
            case "betweenEnd":
                $query = $query->where($column, "<=", $val);
                break;
            case "search":
                $query = $query->where($column, "like", '%' . $val . '%');
                break;
        }
    }

    /**
     * 获取数据
     * @param Get $get
     * @param callable|null $append
     * @param int $resultType
     * @return mixed
     * @throws DbException
     */
    public function get(Get $get, ?callable $append = null, int $resultType = self::RESULT_TYPE_ARRAY): mixed
    {
        /**
         * @var Query $query
         */
        $query = $get->model::newQuery;
        $tableName = $this->getTable($get->model);

        if (count($get->leftJoinWhere) > 0) {
            $get->orderBy[0] = "{$tableName}.{$get->orderBy[0]}";
            if ($get->columns === ["*"]) {
                $get->columns = ["{$tableName}.*"];
            } else {
                foreach ($get->columns as $index => $column) {
                    $get->columns[$index] = "{$tableName}.{$column}";
                }
            }
        }

        foreach ($get->where as $key => $val) {
            if (is_scalar($val)) {
                $val = urldecode((string)$val);
            }
            $key = urldecode($key);
            $args = explode('-', $key);
            if ($val === '') {
                continue;
            }
            if (count($args) != 2) {
                continue;
            }
            $type = $args[0];
            $column = $args[1];

            foreach ($get->leftJoinWhere as $jn) {
                $relatedTableName = $this->getTable($jn['related']);
                foreach ($jn['columns'] as $k => $v) {
                    if ($column == $k) {
                        $query = $query->leftJoin($relatedTableName, "{$relatedTableName}.{$jn['foreignKey']}", "=", "{$tableName}.{$jn["localKey"]}");
                        $this->setWhere($query, $type, "{$relatedTableName}.{$v}", $val);
                        continue 3;
                    }
                }
            }

            $this->setWhere($query, $type, $tableName . "." . $column, $val);
        }

        //追加执行
        if (is_callable($append)) {
            $query = call_user_func($append, $query);
        }

        $query = $query->order($get->orderBy[0] . ' ' . $get->orderBy[1])->distinct(true);

        if ($get->paginate) {
            $paginate = $query->field($get->columns)->paginate(['page' => $get->paginate[0], 'list_rows' => $get->paginate[1]]);
            if ($resultType === DatabaseInterface::RESULT_TYPE_ARRAY) {
                $paginate = $paginate->toArray();
                return ["list" => $paginate['data'], "total" => $paginate['total']];
            }
            return $paginate;
        }

        $result = $query->field($get->columns)->select();
        if ($resultType === DatabaseInterface::RESULT_TYPE_ARRAY) {
            return $result->toArray();
        }

        return $result;
    }

    /**
     * 保存数据
     * @param Save $save
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws JsonException
     * @throws ModelNotFoundException
     */
    public function save(Save $save): mixed
    {
        /**
         * @var Query $query
         */
        $query = $save->model::newQuery;

        $model = $save->id ? $query->find($save->id) : null;
        $modify = false;

        if (!$model) {
            if (!$save->isAddable) {
                throw new JsonException("禁止新增");
            }
            $model = new $save->model;
            $save->isAddCreateTime && ($model->creation_time = DateUtils::current());
        } else {
            if (!$save->isModifiable) {
                throw new JsonException("禁止修改");
            }
            $modify = true;
        }

        $middles = [];

        /**
         * @param string $key
         * @param mixed $value
         * @param array $middles
         * @param mixed $model
         * @param Save $save
         * @return void
         */
        $addColumn = function (string $key, mixed $value, array &$middles, mixed &$model, Save $save) {
            $middle = $save->getMiddle($key);
            if ($middle) {
                $middles[] = ['middle' => $middle, 'data' => $value];
            } else {
                 if (is_scalar($value) && !is_integer($value)) {
                    $value = urldecode($value);
                 }
                $model->$key = $value;
            }
        };

        foreach ($save->map as $key => $item) {
            if ($modify) {
                if (count($save->modifiableWhitelist) > 0 && !in_array($key, $save->modifiableWhitelist)) {
                    continue;
                }
            } else {
                if (count($save->addWhitelist) > 0 && !in_array($key, $save->addWhitelist)) {
                    continue;
                }
            }
            $addColumn($key, $item, $middles, $model, $save);
        }

        foreach ($save->forceMap as $key => $item) {
            $addColumn($key, $item, $middles, $model, $save);
        }

        $model->save();
        $id = $model->id;
        foreach ($middles as $m) {
            $middle = $m['middle'];
            $data = $m['data'];
            if (!empty($data)) {
                //删除中间表关系
                $middle['middle']::where($middle['localKey'], $id)->delete();
            }
            $localKey = $middle['localKey'];
            $foreignKey = $middle['foreignKey'];
            //重新建立模型关系
            foreach ($data as $datum) {
                $middleObject = new $middle['middle'];
                $middleObject->$localKey = $id;
                $middleObject->$foreignKey = $datum;
                $middleObject->save();
            }
        }

        return $model;
    }

    /**
     * 删除数据
     * @param Delete $delete
     * @return int
     * @throws DbException
     * @throws JsonException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function delete(Delete $delete): int
    {
        if (count($delete->list) === 0) {
            throw new JsonException("你还没有选择数据呢");
        }

        $count = 0;
        foreach ($delete->list as $id) {
            /**
             * @var Query $query
             */
            $query = $delete->model::newQuery;
            foreach ($delete->where as $where) {
                $query = $query->where(...$where);
            }

            if ($query->where("id", $id)->find()?->delete()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * 获取排序
     * @param array $map
     * @param string $field
     * @param string $rule
     * @return array|string[]
     */
    public function getOrderBy(array $map, string $field, string $rule = 'desc'): array
    {
        if (isset($map['sort_field']) && isset($map['sort_rule'])) {
            return [$map['sort_field'], $map['sort_rule']];
        }

        return [$field, $rule];
    }
}