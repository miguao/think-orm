<?php

declare(strict_types=1);

namespace app\service\admin\impl;

use app\service\admin\UploadService;
use think\facade\Filesystem;
use think\file\UploadedFile;

class UploadServiceImpl implements UploadService
{
    public function images(UploadedFile $file): string
    {
        $filename = Filesystem::disk('public')->putFile('files', $file, 'md5');
        return '/storage/' . $filename;
    }
}