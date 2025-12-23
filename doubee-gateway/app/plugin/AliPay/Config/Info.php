<?php

declare(strict_types=1);

use app\kernel\plugin\constant\Plugin;

return [
    Plugin::NAME => '支付宝',
    Plugin::DESCRIPTION => '支付宝，全球领先的独立第三方支付平台，致力于为广大用户提供安全快速的电子支付/网上支付/安全支付/手机支付体验，及转账收款/水电煤缴费/信用卡还款/AA收款等生活服务应用。',
    Plugin::AUTHOR => '官方',
    Plugin::VERSION => '1.0.0',
    Plugin::TYPE => 'payment',
    Plugin::OPTIONS => [
        'face' => '订单码支付',
        'pc' => '电脑网站支付',
        'wap' => '手机网站支付',
    ],
];