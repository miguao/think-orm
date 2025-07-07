<?php

declare(strict_types=1);

namespace app\controller\admin\api\user;

use app\controller\AbstractAdminController;
use app\entity\database\Delete;
use app\entity\database\Get;
use app\entity\database\Save;
use app\exception\JsonException;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\SystemUserPermission;
use app\utils\DateUtils;
use Exception;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/admin/api/user/permission")]
#[Middleware(AuthenticationMiddleware::class)]
class PermissionController extends AbstractAdminController
{
    #[Route("GET", "getPermissionList")]
    public function getPermissionList(): Json
    {
        $map = $this->request->get();
        $get = new Get(SystemUserPermission::class);
        $get->setWhere((array)$map);
        $get->setOrderBy("sort", "asc");
        $data = $this->database->get($get);

        return $this->json(data: $data);
    }

    #[Route("*", "savePermission")]
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

    #[Route("DELETE", "deletePermission")]
    public function deletePermission(): Json
    {
        $delete = new Delete(SystemUserPermission::class, (array)$this->request->post("list"));
        $this->database->delete($delete);
        return $this->json(message: "删除成功");
    }
}