<?php

declare (strict_types=1);

namespace app\kernel\route\annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class GetMapping
{
    public string $path;
    public string $method;

    public function __construct(string $path)
    {
        $this->method = strtoupper("GET");
        $this->path = $path;
    }
}