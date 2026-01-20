<?php

declare (strict_types=1);

namespace app\controller\admin\api\institution;

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
use app\model\SystemInstitution;
use app\utils\DateUtils;
use Exception;
use think\response\Json;

#[Controller("/admin/api/institution")]
#[Middleware(AuthenticationMiddleware::class)]
class InstitutionController extends AbstractAdminController
{
    #[GetMapping("getInstitutionList")]
    public function getInstitutionList(): Json
    {
        $map = $this->request->get();
        $get = new Get(SystemInstitution::class);
        $get->setWhere((array)$map);
        $get->setOrderBy("sort", "desc");
        $get->setOrderBy("id", "asc");
        $get->setColumn("id", "parent_id", "name", "full_name", "code", "type", "sort", "creation_time", "status");
        $data = $this->database->get($get);

        return $this->json(data: $data);
    }

    #[RequestMapping("saveInstitution", ["POST", "PUT"])]
    public function saveInstitution(): Json
    {
        $map = $this->request->post();
        $save = new Save(SystemInstitution::class);
        $save->setMap($map);
        $save->enableCreateTime();
        try {
            $this->database->save($save);
        } catch (Exception $exception) {
            throw new JsonException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json(message: "保存成功");
    }

    #[DeleteMapping("deleteInstitution")]
    public function deleteInstitution(): Json
    {
        $delete = new Delete(SystemInstitution::class, (array)$this->request->post("list"));
        $this->database->delete($delete);
        return $this->json(message: "删除成功");
    }
}