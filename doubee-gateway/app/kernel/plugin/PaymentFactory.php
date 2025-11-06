<?php

declare (strict_types=1);

namespace app\kernel\plugin;

use app\kernel\component\Singleton;
use app\kernel\plugin\handler\Payment;

class PaymentFactory
{
    use Singleton;

    /**
     * 获取支付处理器
     * @param string $identifier
     * @return Payment|null
     */
    public function getHandler(string $identifier): ?Payment
    {
        $plugin = PluginFactory::getInstance()->getPlugin($identifier);
        if (!$plugin) {
            return null;
        }

        if (!isset($plugin->handler) || !isset($plugin->handler[Payment::class])) {
            return null;
        }

        $handler = $plugin->handler[Payment::class];
        if (!class_exists($handler)) {
            return null;
        }

        return new $handler();
    }
}