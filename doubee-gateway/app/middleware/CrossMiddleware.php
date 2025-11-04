<?php

namespace app\middleware;

use app\kernel\contract\MiddlewareInterface;
use Closure;
use think\Request;
use think\Response;

class CrossMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $closure): Response
    {
        return $closure($request);
    }
}