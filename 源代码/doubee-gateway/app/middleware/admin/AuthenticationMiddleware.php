<?php

declare(strict_types=1);

namespace app\middleware\admin;

use app\constant\StatusCode;
use app\constant\SystemUserStatus;
use app\exception\UnauthorizedException;
use app\kernel\context\Context;
use app\kernel\contract\MiddlewareInterface;
use app\model\SystemUser;
use app\utils\AesUtils;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Request;
use think\Response;

class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * 鉴权中间件
     * @param Request $request
     * @param Closure $closure
     * @return Response
     * @throws UnauthorizedException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function handle(Request $request, Closure $closure): Response
    {
        $authorization = request()->header("Authorization");
        if (empty($authorization)) {
            throw new UnauthorizedException(StatusCode::UNAUTHORIZED->getMessage());
        }

        if (!str_contains($authorization, "Bearer ")) {
            throw new UnauthorizedException(StatusCode::UNAUTHORIZED->getMessage());
        }

        $token = substr($authorization, 7);
        try {
            $payload = JWT::decode($token, new Key(env("SYSTEM_JWT_KEY"), "HS256"));
            $userId = AesUtils::decrypt($payload->userId);
        } catch (Exception $exception) {
            throw new UnauthorizedException($exception->getMessage());
        }

        $user = SystemUser::query()->find($userId);
        if (!$user) {
            throw new UnauthorizedException("用户不存在");
        }

        if ($user->status !== SystemUserStatus::NORMAL->value) {
            throw new UnauthorizedException("用户异常，请重新登录！");
        }

        $request->systemUserId = $user->id;
        $request->systemUserInfo = $user->toArray();

        Context::set(Request::class, $request);

        return $closure($request);
    }
}