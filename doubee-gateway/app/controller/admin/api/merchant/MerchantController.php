<?php

declare(strict_types=1);

namespace app\controller\admin\api\merchant;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\entity\database\Save;
use app\exception\JsonException;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\Merchant;
use app\service\merchant\UserService;
use app\utils\DateUtils;
use app\utils\StringUtils;
use Exception;
use think\annotation\Inject;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/admin/api/merchant")]
#[Middleware(AuthenticationMiddleware::class)]
class MerchantController extends AbstractAdminController
{
    #[Inject]
    protected UserService $userService;

    #[Route("GET", "getMerchantList")]
    public function getMerchantList(): Json
    {
        $map = $this->request->all();
        $get = new Get(Merchant::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $get->setColumn("id", "merchant_no", "phone", "email", "balance", "freeze_balance", "creation_time", "status");
        $data = $this->database->get($get);

        return $this->json(data: $data);
    }

    #[Route("*", "saveMerchant")]
    public function saveMerchant(): Json
    {
        $map = $this->request->post();
        if (!isset($map['id'])) {
            $this->validator((array)$map, ['password' => 'require'], ['password.require' => '密码不能为空']);
            $map['merchant_no'] = StringUtils::generateUniqueMerchantNumber();
            $map['salting'] = StringUtils::generateRandStr();
            $map['password'] = StringUtils::generatePassword($map['password'], $map['salting']);
            $map['creation_time'] = DateUtils::current();
        } else {
            if (isset($map['password']) && $map['password'] !== "") {
                $user = Merchant::query()->find($map['id']);
                $map['password'] = StringUtils::generatePassword($map['password'], $user->salting);
            }
        }

        $save = new Save(Merchant::class);
        $save->setMap($map);
        $save->addForceMap("creation_time", DateUtils::current());
        try {
            $model = $this->database->save($save);
            // 创建关联应用
            if (!isset($map['id'])) {
                $this->userService->createWithApplication($model);
            }
        } catch (Exception $exception) {
            throw new JsonException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json(message: "保存成功");
    }
}