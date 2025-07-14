<?php

declare(strict_types=1);

use think\facade\Route;

Route::any('plugin/:name/Icon.ico', function ($name) {
    $filePath = app_path("plugin/{$name}") . "Icon.ico";
    if (!file_exists($filePath)) {
        abort(404, 'File not found');
    }
    return download($filePath, 'Icon.ico')->force(false);
});