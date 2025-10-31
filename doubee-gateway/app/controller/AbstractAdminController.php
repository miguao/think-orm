<?php

declare(strict_types=1);

namespace app\controller;

use app\kernel\context\Context;
use app\kernel\database\contract\DatabaseInterface;
use app\kernel\route\annotation\Inject;
use think\Request as ServerRequest;

abstract class AbstractAdminController extends AbstractController
{
    #[Inject]
    protected DatabaseInterface $database;

    /**
     * 获取用户信息
     * @return array|null
     */
    public function getUserInfo(): ?array
    {
        $serverRequest = Context::get(ServerRequest::class);
        return $serverRequest->systemUserInfo;
    }

    /**
     * 获取用户ID
     * @return int|null
     */
    public function getUserId(): ?int
    {
        $serverRequest = Context::get(ServerRequest::class);
        return $serverRequest->systemUserId;
    }
}