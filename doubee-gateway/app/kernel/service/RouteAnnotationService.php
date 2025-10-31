<?php

declare (strict_types=1);

namespace app\kernel\service;


use app\kernel\route\provider\RouteAnnotationProvider;
use think\Service;

class RouteAnnotationService extends Service
{
    /**
     * 启动服务
     * @return void
     */
    public function boot(): void
    {
        // 系统启动后执行注解扫描注册
        RouteAnnotationProvider::register();
    }
}