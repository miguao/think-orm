<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * @property int $id
 * @property int $parent_id
 * @property string $icon
 * @property string $name
 * @property string $path
 * @property string $component
 * @property string $authority
 * @property int $type
 * @property int $open_type
 * @property int $hide
 * @property int $sort
 * @property array $metadata
 * @property string $creation_time
 * @property int $status
 */
class SystemUserPermission extends Model
{
}
