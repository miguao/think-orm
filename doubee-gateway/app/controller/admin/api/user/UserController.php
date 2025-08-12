<?php

declare (strict_types=1);

namespace app\controller\admin\api\user;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\entity\database\Save;
use app\entity\database\Delete;
use app\exception\JsonException;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\SystemUser;
use app\model\SystemUserRoleRelation;
use app\utils\DateUtils;
use app\utils\StringUtils;
use Exception;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\db\Query;
use think\response\Json;

#[Group("/admin/api/user")]
#[Middleware(AuthenticationMiddleware::class)]
class UserController extends AbstractAdminController
{
    #[Route("GET", "getUserList")]
    public function getUserList(): Json
    {
        $map = $this->request->get();
        $get = new Get(SystemUser::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $get->setColumn("id", "institution_id", "email", "nickname", "login_time", "last_login_time", "login_ip", "last_login_ip", "login_ua", "last_login_ua", "creation_time", "status");
        $data = $this->database->get($get, function (Query $query) {
            return $query->with(['roles']);
        });

        return $this->json(data: $data);
    }

    #[Route("*", "saveUser")]
    public function saveUser(): Json
    {
        $map = $this->request->post();
        if (!isset($map['id'])) {
            $this->validator((array)$map, ['password' => 'require'], ['password.require' => '密码不能为空']);
            $map['salting'] = StringUtils::generateRandStr();
            $map['password'] = StringUtils::generatePassword($map['password'], $map['salting']);
            $map['creation_time'] = DateUtils::current();
        } else {
            if (isset($map['password']) && $map['password'] !== "") {
                $user = SystemUser::query()->find($map['id']);
                $map['password'] = StringUtils::generatePassword($map['password'], $user->salting);
            }
        }

        $save = new Save(SystemUser::class);
        $save->setMap($map);
        $save->setMiddle('roles', SystemUserRoleRelation::class, "role_id", "user_id");
        try {
            $this->database->save($save);
        } catch (Exception $exception) {
            throw new JsonException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json(message: "保存成功");
    }

    #[Route("DELETE", "deleteUser")]
    public function deleteUser(): Json
    {
        $delete = new Delete(SystemUser::class, (array)$this->request->post("list"));
        $this->database->delete($delete);
        return $this->json(message: "删除成功");
    }

    #[Route("POST", "importUser")]
    public function importUser(): Json
    {
        return $this->json(message: "导入成功");
    }
}