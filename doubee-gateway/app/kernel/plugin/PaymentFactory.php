<?php

declare (strict_types=1);

namespace app\kernel\plugin;

use app\kernel\component\Singleton;
use app\kernel\plugin\handler\Payment;
use app\model\PaymentOrder;

class PaymentFactory
{
    use Singleton;

    /**
     * 获取支付处理器
     * @param string $identifier
     * @param PaymentOrder $paymentOrder
     * @param array $config
     * @param string $clientIp
     * @param float $amount
     * @param string $notificationUrl
     * @param string|null $redirectUrl
     * @return Payment|null
     */
    public function getHandler(
        string       $identifier,
        PaymentOrder $paymentOrder,
        array        $config,
        string       $clientIp,
        float        $amount,
        string       $notificationUrl,
        ?string      $redirectUrl = null
    ): ?Payment
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

        return new $handler($plugin, $paymentOrder, $config, $clientIp, $amount, $notificationUrl, $redirectUrl);
    }
}