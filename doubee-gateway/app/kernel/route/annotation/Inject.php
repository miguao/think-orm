<?php

declare(strict_types=1);

namespace app\kernel\route\annotation;

use Attribute;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Inject
{
    public ?string $class;
    protected static array $cache = [];
    protected static array $visited = [];

    public function __construct(?string $class = null)
    {
        $this->class = $class;
    }

    /**
     * 依赖注入
     * @param object $instance
     * @return void
     */
    public static function handle(object $instance): void
    {
        $app = app();
        $className = get_class($instance);
        $objectId = spl_object_id($instance);

        // 避免同一对象被重复处理导致死循环
        if (isset(self::$visited[$objectId])) {
            return;
        }
        self::$visited[$objectId] = true;

        // 如果缓存中没有类的属性元信息，则反射一次并缓存
        if (!isset(self::$cache[$className])) {
            $reflection = new ReflectionClass($instance);
            $props = [];

            foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED) as $property) {
                $attrs = $property->getAttributes(self::class);
                if (empty($attrs)) {
                    continue;
                }

                /** @var Inject $inject */
                $inject = $attrs[0]->newInstance();
                $type = $inject->class ?? $property->getType()?->getName();
                if (!$type) {
                    throw new RuntimeException("无法确定属性 {$property->getName()} 的注入类型");
                }

                $props[] = [
                    'prop' => $property,
                    'type' => $type,
                ];
            }

            self::$cache[$className] = $props;
        }

        // 使用缓存的 ReflectionProperty 来注入（避免直接访问受保护/私有属性）
        foreach (self::$cache[$className] as $meta) {
            /** @var ReflectionProperty $prop */
            $prop = $meta['prop'];
            $type = $meta['type'];

            // 通过容器实例化依赖
            $dependency = $app->make($type);

            if (is_object($dependency)) {
                self::handle($dependency);
            }

            $prop->setValue($instance, $dependency);
        }

        unset(self::$visited[$objectId]);
    }
}
