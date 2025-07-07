<?php

declare(strict_types=1);

use app\kernel\plugin\constant\Plugin;

return [
    Plugin::NAME => "彩虹易支付",
    Plugin::DESCRIPTION => "解决个人支付难题，一站式聚合接入支付宝,微信,QQ钱包,银联,APP,小程序支付，帮助开发者快速集成到自己相应产品，效率高，见效快，费率低。",
    Plugin::AUTHOR => "官方",
    Plugin::AUTHOR_URL => "/",
    Plugin::VERSION => "1.0.0",
    Plugin::FORM => [
        [
            'name' => 'merchant_id',
            'type' => 'input',
            'title' => '商户ID',
            'placeholder' => '请输入商户ID',
        ],
        [
            'name' => 'merchant_key',
            'type' => 'input',
            'title' => '商户密钥',
            'placeholder' => '请输入商户密钥',
        ],
        [
            'name' => 'gateway',
            'type' => 'input',
            'title' => '网关地址',
            'placeholder' => '请输入网关地址',
        ],
    ],
    Plugin::OPTIONS => [
        "alipay" => "支付宝",
        "wxpay" => "微信支付",
        "qqpay" => "QQ钱包",
    ],
];