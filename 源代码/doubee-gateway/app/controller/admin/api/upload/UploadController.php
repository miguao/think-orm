<?php

declare(strict_types=1);

namespace app\controller\admin\api\upload;

use app\controller\AbstractAdminController;
use app\middleware\admin\AuthenticationMiddleware;
use app\service\admin\UploadService;
use think\annotation\Inject;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use think\annotation\route\Route;
use think\response\Json;

#[Group("/admin/api/upload")]
#[Middleware(AuthenticationMiddleware::class)]
class UploadController extends AbstractAdminController
{
    #[Inject]
    protected UploadService $uploadService;

    #[Route("POST", "upload")]
    public function upload(): Json
    {
        $file = $this->request->file('file');
        $static = $this->uploadService->images($file);
        return $this->json(message: "上传成功", data: ['path' => $static]);
    }
}