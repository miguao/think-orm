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
use function Opis\Closure\{serialize, unserialize};
use ReflectionClass;
use ReflectionMethod;
use think\facade\Route;
use think\facade\Cache;
use think\facade\App;

class RouteAnnotationProvider
{
    const CACHE_KEY = 'route.annotation.cache';
    const HASH_KEY = 'route.annotation.hash';

    public static function register(): void
    {
        $controllerPath = app_path('controller');
        $isDebug = App::isDebug();

        $dirHash = self::dirHash($controllerPath);

        // 生产环境使用缓存闭包
        if (!$isDebug) {
            $cached = unserialize(Cache::get(self::CACHE_KEY));
            $cachedHash = unserialize(Cache::get(self::HASH_KEY));
            if (is_array($cached) && $cachedHash === $dirHash) {
                self::registerCachedClosures($cached);
                return;
            }
        }

        $closures = [];

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($controllerPath));

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') continue;

            $relativePath = str_replace([$controllerPath, DIRECTORY_SEPARATOR], ['', '\\'], $file->getPathname());
            $class = 'app\\controller\\' . str_replace('.php', '', $relativePath);

            if (!class_exists($class)) continue;

            $ref = new ReflectionClass($class);
            $ctrlAttribs = $ref->getAttributes(Controller::class);
            if (empty($ctrlAttribs)) continue;

            $prefix = '/' . trim($ctrlAttribs[0]->newInstance()->prefix ?? '', '/');
            $classMiddleware = array_map(fn($a) => $a->newInstance()->class, $ref->getAttributes(Middleware::class));

            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() !== $class) continue;

                $attributes = array_merge(
                    $method->getAttributes(RequestMapping::class),
                    $method->getAttributes(GetMapping::class),
                    $method->getAttributes(PostMapping::class),
                    $method->getAttributes(PutMapping::class),
                    $method->getAttributes(DeleteMapping::class)
                );

                if (!$attributes) continue;

                $methodMiddleware = array_map(fn($a) => $a->newInstance()->class, $method->getAttributes(Middleware::class));
                $middlewares = array_merge($classMiddleware, $methodMiddleware);

                foreach ($attributes as $attr) {
                    $instance = $attr->newInstance();

                    $methods = match (true) {
                        $instance instanceof RequestMapping => $instance->methods,
                        $instance instanceof GetMapping => ['GET'],
                        $instance instanceof PostMapping => ['POST'],
                        $instance instanceof PutMapping => ['PUT'],
                        $instance instanceof DeleteMapping => ['DELETE']
                    };

                    $path = '/' . trim($instance->path, '/');
                    $fullPath = $prefix . $path;

                    foreach ($methods as $httpMethod) {
                        // 生成闭包
                        $closure = function (...$params) use ($class, $method) {
                            $controller = new $class();
                            Inject::handle($controller);
                            return $controller->{$method->getName()}(...$params);
                        };

                        Route::rule($fullPath, $closure, $httpMethod)->middleware($middlewares);

                        // 缓存闭包
                        $closures[] = [
                            'path' => $fullPath,
                            'method' => $httpMethod,
                            'closure' => $closure,
                            'middleware' => $middlewares,
                        ];
                    }
                }
            }
        }

        if (!$isDebug) {
            Cache::set(self::CACHE_KEY, serialize($closures), 86400 * 30);
            Cache::set(self::HASH_KEY, serialize($dirHash), 86400 * 30);
        }
    }

    private static function registerCachedClosures(array $closures): void
    {
        foreach ($closures as $r) {
            Route::rule($r['path'], $r['closure'], $r['method'])->middleware($r['middleware']);
        }
    }

    private static function dirHash(string $dir): string
    {
        $hash = '';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $hash .= md5_file($file->getPathname());
            }
        }
        return md5($hash);
    }
}