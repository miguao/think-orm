<?php

declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\relation\HasMany;

/**
 * @property int $id
 */
class SystemDictionary extends Model
{
    /**
     * 字典数据
     * @return HasMany
     */
    public function dictionaryData(): HasMany
    {
        return $this->hasMany(SystemDictionaryData::class, 'dictionary_id', 'id');
    }
}