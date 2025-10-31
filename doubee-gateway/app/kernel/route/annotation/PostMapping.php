<?php

namespace app\kernel\route\annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class PostMapping
{
    public string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }
}