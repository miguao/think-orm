<?php

declare (strict_types=1);

namespace app\service\admin\impl;

use app\constant\SystemUserPermissionType;
use app\exception\JsonException;
use app\kernel\route\annotation\Inject;
use app\model\SystemUser;
use app\service\admin\LogService;
use app\service\admin\UserService;
use app\utils\AesUtils;
use app\utils\DateUtils;
use app\utils\StringUtils;
use Firebase\JWT\JWT;
use think\db\Query;
use think\Request;

class UserServiceImpl implements UserService
{
    #[Inject]
    protected Request $request;

    #[Inject]
    protected LogService $logService;

    public function login(string $email, string $password): string
    {
        $user = SystemUser::where("email", $email)->find();
        if (!$user) {
            throw new JsonException("用户不存在");
        }

        if ($user->getAttr("password") != StringUtils::generatePassword(trim($password), $user->getAttr("salting"))) {
            throw new JsonException("密码错误");
        }

        if ($user->getAttr("status") != 1) {
            throw new JsonException("You have been banned");
        }

        return $this->setLoginSuccess($user);
    }

    public function setLoginSuccess(SystemUser $user): string
    {
        $loginTime = DateUtils::current();
        $user->setAttr("last_login_time", $user->getAttr("login_time"));
        $user->setAttr("login_time", $loginTime);
        $user->setAttr("last_login_ip", $user->getAttr("login_ip"));
        $user->setAttr("login_ip", $this->request->ip());
        $user->setAttr("last_login_ua", $user->getAttr("login_ua"));
        $user->setAttr("login_ua", $this->request->header("User-Agent"));
        $user->save();

        $payload = [
            'expire' => time() + (int)env("SYSTEM_JWT_EXPIRE"),
            'loginTime' => $loginTime,
            'userId' => AesUtils::encrypt($user->id),
        ];

        $token = JWT::encode($payload, env("SYSTEM_JWT_KEY"), "HS256");

        $this->logService->createLoginLog($user, $user->getAttr("login_ip"), $user->getAttr("login_ua"));

        return $token;
    }

    public function findByUserPermissions(int $userId): array
    {
        $user = SystemUser::with(['roles' => function (Query $query) {
            $query->with(['permissions' => function (Query $query) {
                $query->where("status", 1)->order("sort", "desc");
            }]);
        }])->find($userId);

        if (!$user) {
            throw new JsonException("用户不存在");
        }

        $menus = [];
        $permissions = [];
        foreach ($user->getAttr("roles") as $role) {
            foreach ($role->getAttr("permissions") as $permission) {
                if ($permission->getAttr("type") == SystemUserPermissionType::API->value) {
                    $permissions[] = [
                        'id' => $permission->getAttr("id"),
                        'path' => $permission->getAttr("path"),
                    ];
                }

                if ($permission->getAttr("type") == SystemUserPermissionType::MENU->value || $permission->getAttr("type") == SystemUserPermissionType::DIRECTORY->value) {
                    $menus[] = [
                        'id' => $permission->getAttr("id"),
                        'parent_id' => $permission->getAttr("parent_id"),
                        'icon' => $permission->getAttr("icon"),
                        'name' => $permission->getAttr("name"),
                        'path' => $permission->getAttr("path"),
                        'component' => $permission->getAttr("component"),
                        'hide' => $permission->getAttr("hide"),
                        'metadata' => $permission->getAttr("metadata"),
                    ];
                }
            }
        }

        return ['permissions' => $permissions, 'menus' => $menus];
    }

    public function logout(int $userId): void
    {

    }
}