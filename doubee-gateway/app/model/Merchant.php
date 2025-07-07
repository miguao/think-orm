<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * @property int $id
 * @property int $merchant_no
 * @property string $phone
 * @property string $password
 * @property string $salting
 * @property string $email
 * @property string $creation_time
 * @property string $balance
 * @property string $freeze_balance
 * @property int $status
 */
class Merchant extends Model
{

}