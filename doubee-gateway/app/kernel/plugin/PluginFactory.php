<?php

declare (strict_types=1);

namespace app\kernel\plugin;

use app\kernel\component\Singleton;
use app\kernel\plugin\entity\Plugin as PluginEntity;
use app\kernel\plugin\entity\Query;
use Symfony\Component\Finder\Finder;
use think\facade\Event;

class PluginFactory
{
    use Singleton;

    /**
     * 获取插件
     * @param string $identifier
     * @return PluginEntity|null
     */
    public function getPlugin(string $identifier): ?PluginEntity
    {
        $pluginBasePath = base_path("plugin/{$identifier}");
        list($info, $form, $handler) = [
            $pluginBasePath . "Config/Info.php",
            $pluginBasePath . "Config/Form.php",
            $pluginBasePath . "Config/Handler.php",
        ];

        if (!file_exists($info)) {
            return null;
        }

        return new PluginEntity(
            $identifier,
            (array)require($info),
            file_exists($form) ? (array)require($form) : [],
            file_exists($handler) ? (array)require($handler) : []
        );
    }

    /**
     * 获取已安装插件
     * @param Query $query
     * @return array
     */
    public function getInstalledPlugins(Query $query): array
    {
        $pluginBasePath = base_path('plugin');
        $finder = is_dir($pluginBasePath) ? Finder::create()->in($pluginBasePath)->depth("== 0")->directories() : [];

        $data = [];
        foreach ($finder as $item) {
            $plugin = $this->getPlugin($item->getFilename());
            if (!$plugin) {
                continue;
            }

            $data[] = $plugin;
        }

        $offset = ($query->paginate[0] - 1) * $query->paginate[1];
        $data = array_slice($data, $offset, $query->paginate[1]);
        $total = count($data);

        return ['list' => $data, 'total' => $total];
    }

    /**
     * 触发事件
     * @param string $hook
     * @param array $params
     * @return mixed
     */
    public function trigger(string $hook, array $params = []): mixed
    {
        return Event::trigger('PluginEvent', ['hook' => $hook, 'params' => $params]);
    }
}