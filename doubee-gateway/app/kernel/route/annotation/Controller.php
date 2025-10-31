<?php

declare (strict_types=1);

namespace app\kernel\route\annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
    public string $prefix;

    public function __construct(string $prefix = "")
    {
        $this->prefix = $prefix;
    }
}