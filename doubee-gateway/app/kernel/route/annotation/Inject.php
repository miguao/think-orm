<?php

declare (strict_types=1);

namespace app\kernel\route\annotation;

use Attribute;
use ReflectionClass;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Inject
{
    public ?string $class;

    public function __construct(string $class = null)
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
        $reflection = new ReflectionClass($instance);
        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED) as $property) {
            $attrs = $property->getAttributes(Inject::class);
            if (empty($attrs)) continue;

            /** @var Inject $inject */
            $inject = $attrs[0]->newInstance();

            $type = $inject->class ?? $property->getType()?->getName();
            if ($type === null) {
                throw new \RuntimeException("Cannot determine class to inject for property {$property->getName()}");
            }

            $propValue = $property->isInitialized($instance) ? $property->getValue($instance) : null;
            if ($propValue === null) {
                $property->setValue($instance, new $type());
            }
        }
    }
}