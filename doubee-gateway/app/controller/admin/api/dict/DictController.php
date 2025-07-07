<?php

declare(strict_types=1);

namespace app\controller\admin\api\dict;

use app\controller\AbstractAdminController;
use app\entity\database\Delete;
use app\entity\database\Get;
use app\entity\database\Save;
use app\exception\JsonException;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\SystemDict;
use app\utils\DateUtils;
use app\service\admin\DictService;
use Exception;
use think\annotation\Inject;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\db\Query;
use think\response\Json;

#[Group("/admin/api/dict")]
#[Middleware(AuthenticationMiddleware::class)]
class DictController extends AbstractAdminController
{
    #[Inject]
    protected DictService $dictService;

    #[Route("GET", "getDictionaryByCode")]
    public function getDictionaryByCode(): Json
    {
        $map = $this->request->get();
        $this->validator((array)$map, ['dict' => 'require'], ['dict.require' => '字典编码不能为空']);

        $data = $this->dictService->getDict((string)$map['dict'], (string)$this->request->get("keywords"));
        return $this->json(data: (array)$data);
    }

    #[Route("GET", "getDictList")]
    public function getDictList(): Json
    {
        $map = $this->request->get();
        $get = new Get(SystemDict::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get, function (Query $query) {
            return $query->with(['dictData']);
        });

        return $this->json(data: $data);
    }

    #[Route("*", "saveDict")]
    public function saveDict(): Json
    {
        $map = $this->request->post();
        $save = new Save(SystemDict::class);
        $save->setMap($map);
        $save->addForceMap("creation_time", DateUtils::current());
        try {
            $this->database->save($save);
        } catch (Exception $exception) {
            throw new JsonException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json(message: "保存成功");
    }

    #[Route("DELETE", "deleteDict")]
    public function deleteDict(): Json
    {
        $delete = new Delete(SystemDict::class, (array)$this->request->post("list"));
        $this->database->delete($delete);
        return $this->json(message: "删除成功");
    }
}