<?php

declare(strict_types=1);

namespace app\controller\admin\api\user;

use app\controller\AbstractAdminController;
use app\entity\database\Delete;
use app\entity\database\Get;
use app\entity\database\Save;
use app\exception\JsonException;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\DeleteMapping;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Middleware;
use app\kernel\route\annotation\RequestMapping;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\SystemUserPermission;
use app\utils\DateUtils;
use Exception;
use think\response\Json;

#[Controller("/admin/api/user/permission")]
#[Middleware(AuthenticationMiddleware::class)]
class PermissionController extends AbstractAdminController
{
    #[GetMapping("getPermissionList")]
    public function getPermissionList(): Json
    {
        $map = $this->request->get();
        $get = new Get(SystemUserPermission::class);
        $get->setWhere((array)$map);
        $get->setOrderBy("sort", "desc");
        $data = $this->database->get($get);

        return $this->json(data: $data);
    }

    #[RequestMapping("savePermission", ["POST", "PUT"])]
    public function savePermission(): Json
    {
        $map = $this->request->post();
        $save = new Save(SystemUserPermission::class);
        $save->setMap($map);
        $save->addForceMap("creation_time", DateUtils::current());
        try {
            $this->database->save($save);
        } catch (Exception $exception) {
            throw new JsonException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json(message: "保存成功");
    }

    #[DeleteMapping("deletePermission")]
    public function deletePermission(): Json
    {
        $delete = new Delete(SystemUserPermission::class, (array)$this->request->post("list"));
        $this->database->delete($delete);
        return $this->json(message: "删除成功");
    }
}