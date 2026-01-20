<?php

declare(strict_types=1);

namespace app\controller\admin\api\merchant;

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
use app\model\MerchantGroup;
use app\model\MerchantPermission;
use app\model\MerchantPermissionRelation;
use app\utils\DateUtils;
use Exception;
use think\response\Json;

#[Controller("/admin/api/merchant/group")]
#[Middleware(AuthenticationMiddleware::class)]
class GroupController extends AbstractAdminController
{
    #[GetMapping("getGroupList")]
    public function getGroupList(): Json
    {
        $map = $this->request->all();
        $get = new Get(MerchantGroup::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get);

        return $this->json(data: $data);
    }

    #[RequestMapping("saveGroup", ["POST", "PUT"])]
    public function saveGroup(): Json
    {
        $map = $this->request->post();
        $save = new Save(MerchantGroup::class);
        $save->setMap($map);
        $save->setMiddle("permissions", MerchantPermissionRelation::class, "permission_id", "group_id");

        $save->enableCreateTime();
        try {
            $this->database->save($save);
        } catch (Exception $exception) {
            throw new JsonException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json(message: "保存成功");
    }

    #[DeleteMapping("deleteGroup")]
    public function deleteGroup(): Json
    {
        $delete = new Delete(MerchantGroup::class, (array)$this->request->post("list"));
        $this->database->delete($delete);
        return $this->json(message: "删除成功");
    }

    #[GetMapping("getPermissionsByGroupId")]
    public function getPermissionsByGroupId(): Json
    {
        $map = $this->request->get();
        $this->validator((array)$map, ['group_id' => 'require'], ['group_id.require' => '用户组ID不能为空']);

        $get = new Get(MerchantPermission::class);
        $get->setWhere((array)$map);
        $get->setOrderBy('sort', 'desc');
        $data = $this->database->get($get);

        $groupId = (int)$map['group_id'];
        $roleData = MerchantGroup::query()->with(['permissions'])->find($groupId);
        if (!$roleData) {
            throw new JsonException("用户组不存在");
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