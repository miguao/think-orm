<?php

declare (strict_types=1);

namespace app\controller\admin\api\user;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\entity\database\Save;
use app\entity\database\Delete;
use app\exception\JsonException;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\DeleteMapping;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Middleware;
use app\kernel\route\annotation\PostMapping;
use app\kernel\route\annotation\RequestMapping;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\SystemUser;
use app\model\SystemUserRoleRelation;
use app\utils\DateUtils;
use app\utils\StringUtils;
use Exception;
use think\db\Query;
use think\response\Json;

#[Controller("/admin/api/user")]
#[Middleware(AuthenticationMiddleware::class)]
class UserController extends AbstractAdminController
{
    #[GetMapping("getUserList")]
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

    #[RequestMapping("saveUser", ["POST", "PUT"])]
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
                $user = SystemUser::find($map['id']);
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

    #[DeleteMapping("deleteUser")]
    public function deleteUser(): Json
    {
        $delete = new Delete(SystemUser::class, (array)$this->request->post("list"));
        $this->database->delete($delete);
        return $this->json(message: "删除成功");
    }

    #[PostMapping("importUser")]
    public function importUser(): Json
    {
        return $this->json(message: "导入成功");
    }
}