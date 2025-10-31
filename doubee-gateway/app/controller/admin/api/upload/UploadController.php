<?php

declare(strict_types=1);

namespace app\controller\admin\api\upload;

use app\controller\AbstractAdminController;
use app\kernel\route\annotation\Controller;
use app\kernel\route\annotation\Inject;
use app\kernel\route\annotation\Middleware;
use app\kernel\route\annotation\PostMapping;
use app\middleware\admin\AuthenticationMiddleware;
use app\service\admin\UploadService;
use think\response\Json;

#[Controller("/admin/api/upload")]
#[Middleware(AuthenticationMiddleware::class)]
class UploadController extends AbstractAdminController
{
    #[Inject]
    protected UploadService $uploadService;

    #[PostMapping("upload")]
    public function upload(): Json
    {
        $file = $this->request->file('file');
        $static = $this->uploadService->images($file);
        return $this->json(message: "上传成功", data: ['path' => $static]);
    }
}