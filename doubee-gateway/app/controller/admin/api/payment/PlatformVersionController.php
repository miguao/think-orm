<?php

declare(strict_types=1);

namespace app\controller\admin\api\payment;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\entity\database\Save;
use app\exception\JsonException;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\PaymentPlatformVersion;
use app\utils\DateUtils;
use Exception;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/admin/api/payment/platform/version")]
#[Middleware(AuthenticationMiddleware::class)]
class PlatformVersionController extends AbstractAdminController
{
    #[Route("GET", "getVersionList")]
    public function getVersionList(): Json
    {
        $map = $this->request->all();
        $this->validator((array)$map, [
            'platform_id' => 'require'
        ], [
            'platform_id.require' => '平台ID不能为空'
        ]);
        $get = new Get(PaymentPlatformVersion::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get);

        return $this->json(data: $data);
    }

    #[Route("*", "saveVersion")]
    public function saveVersion(): Json
    {
        $map = $this->request->post();
        $save = new Save(PaymentPlatformVersion::class);
        $save->setMap($map);
        $save->addForceMap("creation_time", DateUtils::current());
        try {
            $this->database->save($save);
        } catch (Exception $exception) {
            throw new JsonException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json(message: "保存成功");
    }
}