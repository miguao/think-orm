<?php

declare(strict_types=1);

namespace app\kernel\route\provider;

use app\kernel\route\annotation\{
    Controller,
    RequestMapping,
    GetMapping,
    PostMapping,
    PutMapping,
    DeleteMapping,
    Middleware,
    Inject
};
use ReflectionClass;
use ReflectionMethod;
use think\facade\Route;

class RouteAnnotationProvider
{
    public static function register(): void
    {
        $controllerPath = app_path('controller');

        $directory = new \RecursiveDirectoryIterator($controllerPath);
        $iterator = new \RecursiveIteratorIterator($directory);

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace([$controllerPath, DIRECTORY_SEPARATOR], ['', '\\'], $file->getPathname());
            $class = 'app\\controller\\' . str_replace('.php', '', $relativePath);

            if (!class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            // 获取控制器前缀
            $classAttributes = $reflection->getAttributes(Controller::class);
            $prefix = '';
            if (!empty($classAttributes)) {
                $prefix = $classAttributes[0]->newInstance()->prefix;
            }
            // 自动处理前缀斜杠
            $prefix = $prefix ? '/' . trim($prefix, '/') : '';

            // 获取控制器级中间件
            $classMiddlewareAttrs = $reflection->getAttributes(Middleware::class);
            $classMiddleware = [];
            foreach ($classMiddlewareAttrs as $attr) {
                $classMiddleware[] = $attr->newInstance()->class;
            }

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                // 获取路由注解
                $attributes = array_merge(
                    $method->getAttributes(RequestMapping::class),
                    $method->getAttributes(GetMapping::class),
                    $method->getAttributes(PostMapping::class),
                    $method->getAttributes(PutMapping::class),
                    $method->getAttributes(DeleteMapping::class)
                );

                foreach ($attributes as $attr) {
                    $instance = $attr->newInstance();

                    if ($instance instanceof RequestMapping) {
                        $httpMethod = $instance->method;
                        $path = $instance->path;
                    } elseif ($instance instanceof GetMapping) {
                        $httpMethod = 'GET';
                        $path = $instance->path;
                    } elseif ($instance instanceof PostMapping) {
                        $httpMethod = 'POST';
                        $path = $instance->path;
                    } elseif ($instance instanceof PutMapping) {
                        $httpMethod = 'PUT';
                        $path = $instance->path;
                    } elseif ($instance instanceof DeleteMapping) {
                        $httpMethod = 'DELETE';
                        $path = $instance->path;
                    } else {
                        continue;
                    }

                    // 自动处理方法路径斜杠
                    $path = '/' . trim($path, '/');

                    // 拼接完整路由
                    $fullPath = $prefix . $path;

                    // 获取方法级中间件
                    $methodMiddlewareAttrs = $method->getAttributes(Middleware::class);
                    $methodMiddleware = [];
                    foreach ($methodMiddlewareAttrs as $attrMiddleware) {
                        $methodMiddleware[] = $attrMiddleware->newInstance()->class;
                    }

                    // 合并控制器级和方法级中间件
                    $middlewares = array_merge($classMiddleware, $methodMiddleware);

                    // 注册路由，闭包内自动实例化控制器并注入依赖
                    Route::rule($fullPath, function (...$params) use ($class, $method) {
                        $controller = new $class();
                        Inject::handle($controller);
                        return $controller->{$method->getName()}(...$params);
                    }, $httpMethod)->middleware($middlewares);
                }
            }
        }
    }
}