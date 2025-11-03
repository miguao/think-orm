<?php

declare(strict_types=1);

use think\middleware\AllowCrossDomain;
use think\middleware\CheckRequestCache;

return [
    CheckRequestCache::class,
    AllowCrossDomain::class,
];