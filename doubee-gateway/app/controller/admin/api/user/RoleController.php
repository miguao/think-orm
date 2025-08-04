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
use app\model\SystemUserPermissionRelation;
use app\model\SystemUserRole;
use app\utils\DateUtils;
use Exception;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\db\Query;
use think\response\Json;

#[Group("/admin/api/user/role")]
#[Middleware(AuthenticationMiddleware::class)]
class RoleController extends AbstractAdminController
{
    #[Route("GET", "getRoleList")]
    public function getRoleList(): Json
    {
        $map = $this->request->get();
        $get = new Get(SystemUserRole::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get, function (Query $query) {
            return $query->with('permissions');
        });

        return $this->json(data: $data);
    }

    #[Route("*", "saveRole")]
    public function saveRole(): Json
    {
        $map = $this->request->post();
        $save = new Save(SystemUserRole::class);
        $save->setMap($map);
        $save->setMiddle("auth", SystemUserPermissionRelation::class, "permission_id", "role_id");
        $save->addForceMap("creation_time", DateUtils::current());
        try {
            $this->database->save($save);
        } catch (Exception $exception) {
            throw new JsonException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json(message: "保存成功");
    }

    #[Route("DELETE", "deleteRole")]
    public function deleteRole(): Json
    {
        $delete = new Delete(SystemUserRole::class, (array)$this->request->post("list"));
        $this->database->delete($delete);
        return $this->json(message: "删除成功");
    }

    #[Route("GET", "getRolePermissions")]
    public function getRolePermissions(): Json
    {
        $map = $this->request->get();
        $this->validator((array)$map, ['role_id' => 'require'], ['role_id.require' => '角色ID不能为空']);

        $get = new Get(SystemUserPermission::class);
        $get->setWhere((array)$map);
        $data = $this->database->get($get);

        $roleId = (int)$map['role_id'];
        $roleData = SystemUserRole::query()->with(['permissions'])->find($roleId);
        if (!$roleData) {
            throw new JsonException("角色不存在");
        }

        $roleData = $roleData->toArray();
        if (!empty($roleData['permissions'])) {
            foreach ($roleData['permissions'] as $value) {
                foreach ($data as $key => $permission) {
                    if ($value['id'] == $permission['id']) {
                        $data[$key]['checked'] = true;
                    } else {
                        if (!isset($permission['checked'])) {
                            $data[$key]['checked'] = false;
                        }
                    }
                }
            }
        }

        $data = array_map(function ($item) {
            return [
                'id' => $item['id'],
                'parent_id' => $item['parent_id'],
                'icon' => $item['icon'],
                'name' => $item['name'],
                'checked' => $item['checked'] ?? false,
            ];
        }, $data);

        return $this->json(data: $data);
    }
}