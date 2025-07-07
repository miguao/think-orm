<?php

declare(strict_types=1);

namespace app\constant;

enum MerchantUserStatus: int
{
    case BANNED = 0;
    case NORMAL = 1;
}
