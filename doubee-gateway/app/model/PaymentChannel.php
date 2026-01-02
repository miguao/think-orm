<?php

declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\relation\HasOne;

class PaymentChannel extends Model
{
    protected $type = [
        'config' => 'array'
    ];

    /**
     * 类型信息
     * @return HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(PaymentType::class, "id", "type_id");
    }
}