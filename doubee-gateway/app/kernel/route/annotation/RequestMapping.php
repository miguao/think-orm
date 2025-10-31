<?php

declare (strict_types=1);

namespace app\kernel\route\annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestMapping
{
    public string $method;
    public string $path;

    public function __construct(string $path, string $method = "*")
    {
        $this->method = strtoupper($method);
        $this->path = $path;
    }
}