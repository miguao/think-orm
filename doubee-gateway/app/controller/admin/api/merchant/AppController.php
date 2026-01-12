<?php

declare(strict_types=1);

namespace app\controller\admin\api\merchant;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\entity\database\Save;
use app\exception\JsonException;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\GetMapping;
use app\kernel\route\annotation\Middleware;
use app\kernel\route\annotation\PostMapping;
use app\kernel\route\annotation\RequestMapping;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\MerchantApplication;
use app\utils\DateUtils;
use app\utils\StringUtils;
use Exception;
use think\db\Query;
use think\response\Json;

#[Controller("/admin/api/merchant/app")]
#[Middleware(AuthenticationMiddleware::class)]
class AppController extends AbstractAdminController
{
    #[GetMapping("getAppList")]
    public function getAppList(): Json
    {
        $map = $this->request->all();
        $get = new Get(MerchantApplication::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $raw = [];
        $data = $this->database->get($get, function (Query $query) use (&$raw) {
            // 正在运行的应用
            $raw['app_running_count'] = (clone $query)->where("status", 1)->count();
            // 异常的应用
            $raw['app_stop_count'] = (clone $query)->where("status", 0)->count();
            // 待审应用
            $raw['app_awaiting_count'] = (clone $query)->where("status", 2)->count();
            // 全部应用
            $raw['app_all_count'] = (clone $query)->count();

            return $query->with(['merchant']);
        });

        return $this->json(data: $data, ext: $raw);
    }

    #[RequestMapping("saveApp", ["POST", "PUT"])]
    public function saveApp(): Json
    {
        $map = $this->request->post();
        if (!isset($map['id'])) {
            $map['application_no'] = StringUtils::generateUniqueAppNumber();
            $map['secret'] = StringUtils::generateRandStr();
        }

        $save = new Save(MerchantApplication::class);
        $save->setMap($map);
        $save->addForceMap("creation_time", DateUtils::current());
        try {
            $this->database->save($save);
        } catch (Exception $exception) {
            throw new JsonException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json(message: "保存成功");
    }

    #[PostMapping("resetKey")]
    public function resetKey(): Json
    {
        $map = $this->request->post();
        $this->validator((array)$map, ['id' => 'require'], ['id.require' => '应用ID不能为空']);

        $application = MerchantApplication::find((int)$map["id"]);
        if (!$application) {
            throw new JsonException("应用不存在");
        }

        try {
            $application->secret = StringUtils::generateRandStr();
            $application->save();
        } catch (Exception $exception) {
            throw new JsonException($exception->getMessage());
        }

        return $this->json(message: "重置成功");
    }
}