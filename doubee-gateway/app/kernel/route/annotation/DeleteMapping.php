<?php

declare (strict_types=1);

namespace app\kernel\route\annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class DeleteMapping
{
    public string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }
}