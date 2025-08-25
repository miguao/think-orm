<?php

declare(strict_types=1);

namespace app\service\admin\impl;

use app\exception\JsonException;
use app\service\admin\UploadService;
use Exception;
use think\facade\Filesystem;
use think\file\UploadedFile;

class UploadServiceImpl implements UploadService
{
    public function images(UploadedFile $file): string
    {
        try {
            $filename = Filesystem::disk('public')->putFile('files', $file, 'md5');
        } catch (Exception $exception) {
            throw new JsonException($exception->getMessage());
        }
        
        return '/storage/' . $filename;
    }
}