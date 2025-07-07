<?php

declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\relation\HasMany;

/**
 * @property int $id
 */
class SystemDict extends Model
{
    /**
     * 字典数据
     * @return HasMany
     */
    public function dictData(): HasMany
    {
        return $this->hasMany(SystemDictData::class, 'dict_id', 'id');
    }
}