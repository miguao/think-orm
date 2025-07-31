<?php

declare (strict_types=1);

namespace app\service\admin\impl;

use app\constant\SystemUserPermissionType;
use app\exception\JsonException;
use app\model\SystemUser;
use app\service\admin\LogService;
use app\service\admin\UserService;
use app\utils\AesUtils;
use app\utils\DateUtils;
use app\utils\StringUtils;
use Firebase\JWT\JWT;
use think\annotation\Inject;
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
        $user = SystemUser::newQuery()->where("email", $email)->find();
        if (!$user) {
            throw new JsonException("用户不存在");
        }

        if ($user->password != StringUtils::generatePassword(trim($password), $user->salting)) {
            throw new JsonException("密码错误");
        }

        if ($user->status != 1) {
            throw new JsonException("You have been banned");
        }

        return $this->setLoginSuccess($user);
    }

    public function setLoginSuccess(SystemUser $user): string
    {
        $loginTime = DateUtils::current();
        $user->last_login_time = $user->login_time;
        $user->login_time = $loginTime;
        $user->last_login_ip = $user->login_ip;
        $user->login_ip = $this->request->ip();
        $user->last_login_ua = $user->login_ua;
        $user->login_ua = $this->request->header("User-Agent");
        $user->save();

        $payload = [
            'expire' => time() + (int)env("SYSTEM_JWT_EXPIRE"),
            'loginTime' => $loginTime,
            'userId' => AesUtils::encrypt($user->id),
        ];

        $token = JWT::encode($payload, env("SYSTEM_JWT_KEY"), "HS256");

        $this->logService->createLoginLog($user, $user->login_ip, $user->login_ua);

        return $token;
    }

    public function findByUserPermissions(int $userId): array
    {
        $user = SystemUser::query()->with(['roles' => function (Query $query) {
            $query->with(['permissions' => function (Query $query) {
                $query->where("status", 1)->order("sort", "desc");
            }]);
        }])->find($userId);

        if (!$user) {
            throw new JsonException("用户不存在");
        }

        $menus = [];
        $permissions = [];
        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                if ($permission->type == SystemUserPermissionType::API->value) {
                    $permissions[] = [
                        'id' => $permission->id,
                        'path' => $permission->path,
                    ];
                }

                if ($permission->type == SystemUserPermissionType::MENU->value || $permission->type == SystemUserPermissionType::DIRECTORY->value) {
                    $menus[] = [
                        'id' => $permission->id,
                        'parent_id' => $permission->parent_id,
                        'icon' => $permission->icon,
                        'name' => $permission->name,
                        'path' => $permission->path,
                        'component' => $permission->component,
                        'hide' => $permission->hide,
                        'metadata' => $permission->metadata,
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