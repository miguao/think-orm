<?php

declare(strict_types=1);

namespace app\constant;

enum SystemUserPermissionType: int
{
    case DIRECTORY = 0;
    case MENU = 1;
    case API = 2;
}