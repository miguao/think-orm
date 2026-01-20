<?php

declare(strict_types=1);

namespace app\controller\admin\api\config;

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
use app\model\SystemConfig;
use app\utils\DateUtils;
use Exception;
use think\db\Query;
use think\response\Json;

#[Controller("/admin/api/config")]
#[Middleware(AuthenticationMiddleware::class)]
class ConfigController extends AbstractAdminController
{
    #[GetMapping("getConfigList")]
    public function getConfigList(): Json
    {
        $map = $this->request->get();
        $get = new Get(SystemConfig::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get, function (Query $query) {
            return $query->with(['configData']);
        });

        return $this->json(data: $data);
    }

    #[RequestMapping("saveConfig", ["POST", "PUT"])]
    public function saveConfig(): Json
    {
        $map = $this->request->post();
        $save = new Save(SystemConfig::class);
        $save->setMap($map);
        $save->enableCreateTime();
        try {
            $this->database->save($save);
        } catch (Exception $exception) {
            throw new JsonException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json(message: "保存成功");
    }

    #[DeleteMapping("deleteConfig")]
    public function deleteConfig(): Json
    {
        $delete = new Delete(SystemConfig::class, (array)$this->request->post("list"));
        $this->database->delete($delete);
        return $this->json(message: "删除成功");
    }
}