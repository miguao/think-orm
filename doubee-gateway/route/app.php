<?php

declare(strict_types=1);

use think\facade\Route;

Route::any("/", [\app\controller\home\HomeController::class, "index"]);