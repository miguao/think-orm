<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

class SystemBehaviorLog extends Model
{
    /**
     * 数据库驱动
     * @var string
     */
    protected $connection = "mongo";
}