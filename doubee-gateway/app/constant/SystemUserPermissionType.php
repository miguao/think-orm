<?php

declare(strict_types=1);

namespace app\constant;

enum SystemUserPermissionType: int
{
    case API = 0;
    case MENU = 1;
}