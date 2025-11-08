<?php

declare (strict_types=1);

namespace app\controller\admin\api\plugin;

use app\controller\AbstractAdminController;
use app\kernel\plugin\entity\Query;
use app\kernel\plugin\PluginFactory;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Middleware;
use app\kernel\route\annotation\PostMapping;
use app\middleware\admin\AuthenticationMiddleware;
use JetBrains\PhpStorm\NoReturn;
use think\response\Json;

#[Controller("/admin/api/plugin")]
//#[Middleware(AuthenticationMiddleware::class)]
class PluginController extends AbstractAdminController
{
    #[GetMapping("getInstalledPlugins")]
    public function getInstalledPlugins(): Json
    {
        $query = new Query();
        $query->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = PluginFactory::getInstance()->getInstalledPlugins($query);
        return $this->json(data: $data);
    }

    #[GetMapping("previewIcon")]
    public function previewIcon()
    {

    }

    #[NoReturn]
    #[PostMapping("startup")]
    public function startup(): void
    {
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', false);
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        header('Access-Control-Allow-Origin: *');

        $path = escapeshellarg(root_path());
        $cmd = "cd {$path} && composer require guzzlehttp/guzzle -vvv";
        $process = popen($cmd . " 2>&1", 'r');

        ob_flush();
        flush();

        while (!feof($process)) {
            $line = fgets($process);
            if ($line !== false) {
                echo $line;
                ob_flush();
                flush();
            }
        }

        pclose($process);
        exit();
    }


    #[PostMapping("stop")]
    public function stop()
    {

    }
}