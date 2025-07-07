<?php

declare(strict_types=1);

namespace app\service\admin;

use think\file\UploadedFile;

interface UploadService
{
    /**
     * 上传图片
     * @param UploadedFile $file
     * @return string
     */
    public function images(UploadedFile $file): string;
}