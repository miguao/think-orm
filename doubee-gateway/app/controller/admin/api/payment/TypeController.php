<?php

declare(strict_types=1);

namespace app\controller\admin\api\payment;

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
use app\model\PaymentType;
use app\utils\DateUtils;
use Exception;
use think\response\Json;

#[Controller("/admin/api/payment/type")]
#[Middleware(AuthenticationMiddleware::class)]
class TypeController extends AbstractAdminController
{
    #[GetMapping("getTypeList")]
    public function getTypeList(): Json
    {
        $map = $this->request->get();
        $get = new Get(PaymentType::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get);

        return $this->json(data: $data);
    }

    #[RequestMapping("saveType", ["POST", "PUT"])]
    public function saveType(): Json
    {
        $map = $this->request->post();
        $save = new Save(PaymentType::class);
        $save->setMap($map);
        $save->addForceMap("creation_time", DateUtils::current());
        try {
            $this->database->save($save);
        } catch (Exception $exception) {
            throw new JsonException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json(message: "保存成功");
    }

    #[DeleteMapping("deleteType")]
    public function deleteType(): Json
    {
        $delete = new Delete(PaymentType::class, (array)$this->request->post("list"));
        $this->database->delete($delete);
        return $this->json(message: "删除成功");
    }
}