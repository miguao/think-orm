<?php

declare(strict_types=1);

namespace app\controller\admin\api\dictionary;

use app\controller\AbstractAdminController;
use app\entity\database\Delete;
use app\entity\database\Get;
use app\entity\database\Save;
use app\exception\JsonException;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\SystemDictionaryData;
use app\utils\DateUtils;
use Exception;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/admin/api/dictionary/data")]
#[Middleware(AuthenticationMiddleware::class)]
class DataController extends AbstractAdminController
{
    #[Route("GET", "getDataList")]
    public function getDataList(): Json
    {
        $map = $this->request->get();
        $get = new Get(SystemDictionaryData::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get);

        return $this->json(data: $data);
    }

    #[Route("*", "saveData")]
    public function saveData(): Json
    {
        $map = $this->request->post();
        $this->validator((array)$map, [
            'dictionary_id' => 'require|integer'
        ], [
            'dictionary_id.require' => '数据ID不能为空',
        ]);

        $save = new Save(SystemDictionaryData::class);
        $save->setMap($map);
        $save->addForceMap("creation_time", DateUtils::current());
        try {
            $this->database->save($save);
        } catch (Exception $exception) {
            throw new JsonException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json(message: "保存成功");
    }

    #[Route("DELETE", "deleteData")]
    public function deleteData(): Json
    {
        $delete = new Delete(SystemDictionaryData::class, (array)$this->request->post("list"));
        $this->database->delete($delete);
        return $this->json(message: "删除成功");
    }
}