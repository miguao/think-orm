<?php

declare(strict_types=1);

namespace app\controller\admin\api\merchant;

use app\controller\AbstractAdminController;
use app\entity\database\Delete;
use app\entity\database\Get;
use app\entity\database\Save;
use app\exception\JsonException;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\MerchantGroup;
use app\utils\DateUtils;
use Exception;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/admin/api/merchant/group")]
#[Middleware(AuthenticationMiddleware::class)]
class GroupController extends AbstractAdminController
{
    #[Route("GET", "getGroupList")]
    public function getGroupList(): Json
    {
        $map = $this->request->all();
        $get = new Get(MerchantGroup::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get);

        return $this->json(data: $data);
    }

    #[Route("*", "saveGroup")]
    public function saveGroup(): Json
    {
        $map = $this->request->post();
        $save = new Save(MerchantGroup::class);
        $save->setMap($map);
        $save->addForceMap("creation_time", DateUtils::current());
        try {
            $this->database->save($save);
        } catch (Exception $exception) {
            throw new JsonException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json(message: "保存成功");
    }

    #[Route("DELETE", "deleteGroup")]
    public function deleteGroup(): Json
    {
        $delete = new Delete(MerchantGroup::class, (array)$this->request->post("list"));
        $this->database->delete($delete);
        return $this->json(message: "删除成功");
    }
}