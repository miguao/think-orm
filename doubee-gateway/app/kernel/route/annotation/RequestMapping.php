<?php

declare(strict_types=1);

namespace app\kernel\route\annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestMapping
{
    public array $methods;

    public string $path;

    public function __construct(string $path, string|array $method = '*')
    {
        $this->path = $path;

        $methods = (array)$method;
        $this->methods = ($method === '*') ? ['*'] : array_map('strtoupper', $methods);
    }
}
