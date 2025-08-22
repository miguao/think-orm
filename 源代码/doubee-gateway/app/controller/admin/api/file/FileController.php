<?php

declare(strict_types=1);

namespace app\controller\admin\api\file;

use app\controller\AbstractAdminController;
use app\entity\database\Get;
use app\middleware\admin\AuthenticationMiddleware;
use app\model\File;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/admin/api/file")]
#[Middleware(AuthenticationMiddleware::class)]
class FileController extends AbstractAdminController
{
    #[Route("GET", "getFileList")]
    public function getFileList(): Json
    {
        $map = $this->request->get();
        $get = new Get(File::class);
        $get->setWhere((array)$map);
        $get->setPaginate((int)$this->request->get("page"), (int)$this->request->get("limit"));
        $data = $this->database->get($get);

        return $this->json(data: $data);
    }
}