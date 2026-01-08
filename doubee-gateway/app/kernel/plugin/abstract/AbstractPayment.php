<?php

declare (strict_types=1);

namespace app\kernel\plugin\abstract;

use app\job\OrderNotificationJob;
use app\kernel\plugin\entity\Plugin;
use app\kernel\plugin\handler\Payment;
use app\model\PaymentOrder;
use app\utils\DateUtils;
use Exception;
use think\facade\Db;
use think\facade\Queue;

abstract class AbstractPayment implements Payment
{
    /**
     * 插件信息
     * @var Plugin
     */
    protected Plugin $plugin;

    /**
     * 订单信息
     * @var PaymentOrder
     */
    protected PaymentOrder $order;

    /**
     * 配置信息
     * @var array
     */
    protected array $config;

    /**
     * 客户端IP
     * @var string|null
     */
    protected ?string $clientIp;

    /**
     * 支付金额
     * @var float|null
     */
    protected ?float $amount;

    /**
     * 异步通知地址
     * @var string|null
     */
    protected ?string $notificationUrl;

    /**
     * 同步跳转地址
     * @var string|null
     */
    protected ?string $redirectUrl;

    /**
     * 构造函数
     * @param Plugin $plugin
     * @param PaymentOrder $order
     * @param array $config
     * @param string|null $clientIp
     * @param float|null $amount
     * @param string|null $notificationUrl
     * @param string|null $redirectUrl
     */
    public function __construct(
        Plugin       $plugin,
        PaymentOrder $order,
        array        $config,
        ?string      $clientIp = null,
        ?float       $amount = null,
        ?string      $notificationUrl = null,
        ?string      $redirectUrl = null
    )
    {
        $this->plugin = $plugin;
        $this->order = $order;
        $this->config = $config;
        $this->clientIp = $clientIp;
        $this->amount = $amount;
        $this->notificationUrl = $notificationUrl;
        $this->redirectUrl = $redirectUrl;
    }

    public function successful(): void
    {
        Db::transaction(function () {
            $order = $this->order;
            if ($order->status == 0) {
                $order->completion_time = DateUtils::current();
                $order->status = 1;
                $order->save();

                try {
                    Queue::push(OrderNotificationJob::class, $order->toArray());
                } catch (Exception $exception) {
                    // 队列投递失败则写入订单日志
                }
            }
        });
    }
}