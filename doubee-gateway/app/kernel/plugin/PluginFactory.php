<?php

declare (strict_types=1);

namespace app\kernel\plugin;

use app\kernel\plugin\entity\Query;
use Symfony\Component\Finder\Finder;
use think\facade\Event;

class PluginFactory
{
    /**
     * 插件工厂
     * @var PluginFactory|null
     */
    protected static ?PluginFactory $instance = null;

    /**
     * 获取实例
     * @return PluginFactory
     */
    public static function getInstance(): PluginFactory
    {
        return self::$instance ??= new self();
    }

    /**
     * 获取已安装插件
     * @param Query $query
     * @return array
     */
    public function getInstalledPlugins(Query $query): array
    {
        $pluginBasePath = base_path('plugin');
        $plugins = [];

        $finder = new Finder();
        $finder->in($pluginBasePath)->depth('< 2')->directories();

        foreach ($finder as $dir) {
            $folderName = $dir->getFilename();
            $pluginPath = $dir->getRealPath();

            $infoPath = $pluginPath . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Info.php';
            if (!is_file($infoPath)) {
                continue;
            }

            $config = [];
            if (is_file($infoPath)) {
                $maybe = include $infoPath;
                if (is_array($maybe)) {
                    $config = $maybe;
                }
            }

            $plugins[] = [
                'icon' => "/admin/api/plugin/previewIcon?identifier={$folderName}",
                'name' => $config['name'],
                'identifier' => $folderName,
                'type' => $config['type'],
                'author' => $config['author'],
                'description' => $config['description'],
                'version' => $config['version'],
            ];
        }

        if ($query->paginate) {
            $offset = ($query->paginate[0] - 1) * $query->paginate[1];
            $plugins = array_slice($plugins, $offset, $query->paginate[1]);
            $total = count($plugins);

            return ['list' => $plugins, 'total' => $total];
        }

        return $plugins;
    }

    /**
     * 触发事件
     * @param string $hook
     * @param array $params
     * @return void
     */
    public function trigger(string $hook, array $params = []): void
    {
        Event::trigger('PluginEvent', ['hook' => $hook, 'params' => $params]);
    }
}