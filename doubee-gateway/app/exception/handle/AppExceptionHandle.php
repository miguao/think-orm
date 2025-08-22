<?php

declare (strict_types=1);

namespace app\exception\handle;

use app\constant\StatusCode;
use app\exception\JsonException;
use app\exception\UnauthorizedException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

class AppExceptionHandle extends Handle
{
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 异常处理
     * @param $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        switch (true) {
            case $e instanceof JsonException:
            case $e instanceof UnauthorizedException:
                return json(['code' => $e->getCode(), 'message' => $e->getMessage()]);
            case $e instanceof ValidateException:
                return json(['code' => StatusCode::FAILED->value, 'message' => $e->getMessage()]);
        }

        return parent::render($request, $e);
    }
}
