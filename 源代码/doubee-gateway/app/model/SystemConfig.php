<?php

declare(strict_types=1);

namespace app\model;

use app\constant\Component;
use app\utils\ValidatorUtils;
use think\Model;
use think\model\relation\HasMany;

/**
 * @property int $id
 */
class SystemConfig extends Model
{
    /**
     * 获取配置数据
     * @param string $column
     * @param string $direction
     * @return array
     */
    public function getConfigData(string $column = 'sort', string $direction = 'desc'): array
    {
        // 获取关联表
        $configData = $this->configData()->order($column, $direction)->select();
        $processedData = [];
        if ($configData->count() > 0) {
            $insert = [];
            foreach ($configData->toArray() as $data) {
                //验证组件并处理内容数据
                switch ($data['component_type']) {
                    case Component::INPUT->value:
                    case Component::TEXTAREA->value:
                    case Component::FILE->value:
                    case Component::IMAGE->value:
                    case Component::EDITOR->value:
                        $insert[$data['key']] = (string)$data['value'] ?? null; // 将配置值转换为文本类型
                        break;
                    case Component::SELECT->value:
                    case Component::RADIO->value:
                    case Component::SWITCH->value:
                        $insert[$data['key']] = $data['value'] ?? null;
                        break;
                    case Component::CHECKBOX->value:
                        $insert[$data['key']] = [];
                        if (!empty($data['value']) && ValidatorUtils::isJson($data['value'])) {
                            $insert[$data['key']] = json_decode($data['value'], true) ?? [];//解析为数组
                        }
                        break;
                }
            }
            $processedData = $insert;
        }

        return $processedData;
    }

    /**
     * 返回数据配置
     * @return HasMany
     */
    public function configData(): HasMany
    {
        return $this->hasMany(SystemConfigField::class, "config_id", "id");
    }
}