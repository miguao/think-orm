<?php

declare (strict_types=1);

namespace app\kernel\plugin;

use app\kernel\component\Singleton;
use app\kernel\plugin\entity\Plugin as PluginEntity;
use app\kernel\plugin\entity\Query;
use app\kernel\plugin\handler\Payment;
use app\model\PaymentOrder;
use Symfony\Component\Finder\Finder;
use think\facade\Event;

class PluginFactory
{
    use Singleton;

    /**
     * 插件缓存
     * @var array<string, PluginEntity>
     */
    protected array $pluginCache = [];

    /**
     * 获取插件
     * @param string $identifier
     * @return PluginEntity|null
     */
    public function getPlugin(string $identifier): ?PluginEntity
    {
        if (isset($this->pluginCache[$identifier])) {
            return $this->pluginCache[$identifier];
        }

        $basePath = base_path("plugin/{$identifier}/");
        $infoFile = $basePath . 'Config/Info.php';

        if (!is_file($infoFile)) {
            return null;
        }

        $plugin = new PluginEntity(
            $identifier,
            require $infoFile,
            $this->loadConfig($basePath . 'Config/Form.php'),
            $this->loadConfig($basePath . 'Config/Handler.php')
        );

        return $this->pluginCache[$identifier] = $plugin;
    }

    /**
     * 加载配置文件
     * @param string $file
     * @return array
     */
    private function loadConfig(string $file): array
    {
        return is_file($file) ? (array)require $file : [];
    }

    /**
     * 获取支付处理器
     * @param string $identifier
     * @param PaymentOrder $paymentOrder
     * @param array $config
     * @param string|null $clientIp
     * @param float|null $amount
     * @param string|null $notificationUrl
     * @param string|null $redirectUrl
     * @return Payment|null
     */
    public function getPaymentHandler(
        string       $identifier,
        PaymentOrder $paymentOrder,
        array        $config,
        ?string      $clientIp = null,
        ?float       $amount = null,
        ?string      $notificationUrl = null,
        ?string      $redirectUrl = null
    ): ?Payment
    {
        $plugin = $this->getPlugin($identifier);
        if (!$plugin) {
            return null;
        }

        $handlerClass = $plugin->handler[Payment::class] ?? null;
        if (!$handlerClass || !class_exists($handlerClass)) {
            return null;
        }

        return new $handlerClass(
            $plugin,
            $paymentOrder,
            $config,
            $clientIp,
            $amount,
            $notificationUrl,
            $redirectUrl
        );
    }

    /**
     * 获取已安装插件
     * @param Query $query
     * @return array
     */
    public function getInstalledPlugins(Query $query): array
    {
        $plugins = [];

        $pluginBasePath = base_path('plugin');
        if (!is_dir($pluginBasePath)) {
            return ['list' => [], 'total' => 0];
        }

        $finder = Finder::create()
            ->directories()
            ->depth('== 0')
            ->in($pluginBasePath);

        foreach ($finder as $dir) {
            $plugin = $this->getPlugin($dir->getFilename());
            if (!$plugin) {
                continue;
            }

            if ($query->type && $plugin->info['type'] !== $query->type) {
                continue;
            }

            $plugins[] = $plugin;
        }

        $offset = max(0, ($query->paginate[0] - 1) * $query->paginate[1]);
        $total = count(array_slice($plugins, $offset, $query->paginate[1]));

        return [
            'list' => array_slice($plugins, $offset, $query->paginate[1]),
            'total' => $total,
        ];
    }

    /**
     * 触发事件
     * @param string $hook
     * @param array $params
     * @return mixed
     */
    public function trigger(string $hook, array $params = []): mixed
    {
        return Event::trigger('PluginEvent', compact('hook', 'params'));
    }
}