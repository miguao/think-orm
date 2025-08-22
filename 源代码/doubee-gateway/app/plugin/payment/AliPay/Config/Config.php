<?php

declare(strict_types=1);

use app\kernel\plugin\constant\Plugin;

return [
    Plugin::NAME => "支付宝",
    Plugin::DESCRIPTION => "全球领先的独立第三方支付平台，致力于为广大用户提供安全快速的电子支付/网上支付/安全支付/手机支付体验，及转账收款/水电煤缴费/信用卡还款/AA收款等生活服务应用。",
    Plugin::AUTHOR => "官方",
    Plugin::AUTHOR_URL => "https://www.alipay.com/",
    Plugin::VERSION => "1.0.0",
    Plugin::FORM => [
        [
            'name' => 'app_id',
            'type' => 'input',
            'title' => '应用ID',
            'placeholder' => '请输入应用ID',
        ],
        [
            'name' => 'apply_private_key',
            'type' => 'input',
            'title' => '应用私钥',
            'placeholder' => '请输入应用私钥',
        ],
        [
            'name' => 'alipay_public_key',
            'type' => 'input',
            'title' => '支付宝公钥',
            'placeholder' => '请输入支付宝公钥',
        ],
    ],
    Plugin::OPTIONS => [
        "face" => "当面付",
        "pc" => "电脑网站支付",
        "wap" => "手机网站支付",
    ],
];