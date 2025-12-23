<?php

declare(strict_types=1);

namespace app\middleware\merchant;

use app\constant\MerchantUserStatus;
use app\constant\StatusCode;
use app\exception\UnauthorizedException;
use app\kernel\context\Context;
use app\kernel\contract\MiddlewareInterface;
use app\model\Merchant;
use app\utils\AesUtils;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\Request;
use think\Response;

class AuthenticationMiddleware implements MiddlewareInterface
{
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
            $payload = JWT::decode($token, new Key(env("MERCHANT_JWT_KEY"), "HS256"));
            $userId = AesUtils::decrypt($payload->userId);
        } catch (Exception $exception) {
            throw new UnauthorizedException($exception->getMessage());
        }

        $user = Merchant::query()->with(['merchantGroup'])->find($userId);
        if (!$user) {
            throw new UnauthorizedException("用户不存在");
        }

        if ($user->status !== MerchantUserStatus::NORMAL->value) {
            throw new UnauthorizedException("用户异常，请重新登录！");
        }

        $request->merchantUserId = $user->id;
        $request->merchantUserInfo = $user->toArray();

        Context::set(Request::class, $request);

        return $closure($request);
    }
}