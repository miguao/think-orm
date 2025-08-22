<?php

declare(strict_types=1);

namespace app\controller\admin\api\config;

use app\controller\AbstractAdminController;
use app\entity\database\Delete;
use app\entity\database\Get;
use app\entity\database\Save;
use app\exception\JsonException;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\SystemConfig;
use app\utils\DateUtils;
use Exception;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\db\Query;
use think\response\Json;

#[Group("/admin/api/config")]
#[Middleware(AuthenticationMiddleware::class)]
class ConfigController extends AbstractAdminController
{
    #[Route("GET", "getConfigList")]
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

    #[Route("*", "saveConfig")]
    public function saveConfig(): Json
    {
        $map = $this->request->post();
        $save = new Save(SystemConfig::class);
        $save->setMap($map);
        $save->addForceMap("creation_time", DateUtils::current());
        try {
            $this->database->save($save);
        } catch (Exception $exception) {
            throw new JsonException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json(message: "保存成功");
    }

    #[Route("DELETE", "deleteConfig")]
    public function deleteConfig(): Json
    {
        $delete = new Delete(SystemConfig::class, (array)$this->request->post("list"));
        $this->database->delete($delete);
        return $this->json(message: "删除成功");
    }
}