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

        if (is_string($method)) {
            $this->methods = $method === '*' ? ['*'] : [strtoupper($method)];
        } else {
            $this->methods = array_map('strtoupper', $method);
        }
    }
}
