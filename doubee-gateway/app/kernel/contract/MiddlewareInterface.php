<?php

declare(strict_types=1);

namespace app\kernel\contract;

use Closure;
use think\Request;
use think\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, Closure $closure): Response;
}