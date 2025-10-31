<?php

declare(strict_types=1);

namespace app\controller\admin\api\dictionary;

use app\controller\AbstractAdminController;
use app\entity\database\Delete;
use app\entity\database\Get;
use app\entity\database\Save;
use app\exception\JsonException;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\DeleteMapping;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Inject;
use app\kernel\route\annotation\Middleware;
use app\kernel\route\annotation\RequestMapping;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\SystemDictionary;
use app\utils\DateUtils;
use app\service\admin\DictionaryService;
use Exception;
use think\db\Query;
use think\response\Json;

#[Controller("/admin/api/dictionary")]
#[Middleware(AuthenticationMiddleware::class)]
class DictionaryController extends AbstractAdminController
{
    #[Inject]
    protected DictionaryService $dictService;

    #[GetMapping("getDictionaryByCode")]
    public function getDictionaryByCode(): Json
    {
        $map = $this->request->get();
        $this->validator((array)$map, ['code' => 'require'], ['code.require' => '字典编码不能为空']);

        $data = $this->dictService->getDictionary((string)$map['code'], (string)$this->request->get("keywords"));
        return $this->json(data: (array)$data);
    }

    #[GetMapping("getAllDictionaryList")]
    public function getAllDictionaryList(): Json
    {
        $map = $this->request->get();
        $get = new Get(SystemDictionary::class);
        $get->setWhere((array)$map);

        $data = $this->database->get($get);
        return $this->json(data: $data);
    }

    #[GetMapping("getDictionaryList")]
    public function getDictionaryList(): Json
    {
        $map = $this->request->get();
        $get = new Get(SystemDictionary::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get, function (Query $query) {
            return $query->with(['dictionaryData']);
        });

        return $this->json(data: $data);
    }

    #[RequestMapping("saveDictionary", ["POST", "PUT"])]
    public function saveDictionary(): Json
    {
        $map = $this->request->post();
        $save = new Save(SystemDictionary::class);
        $save->setMap($map);
        $save->addForceMap("creation_time", DateUtils::current());
        try {
            $this->database->save($save);
        } catch (Exception $exception) {
            throw new JsonException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json(message: "保存成功");
    }

    #[DeleteMapping("deleteDictionary")]
    public function deleteDictionary(): Json
    {
        $delete = new Delete(SystemDictionary::class, (array)$this->request->post("list"));
        $this->database->delete($delete);
        return $this->json(message: "删除成功");
    }
}