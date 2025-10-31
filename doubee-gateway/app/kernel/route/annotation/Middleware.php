<?php

declare (strict_types=1);

namespace app\kernel\route\annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Middleware
{
    public string $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }
}