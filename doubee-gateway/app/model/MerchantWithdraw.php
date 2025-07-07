<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * @property int $id
 * @property int $merchant_id
 * @property int $card_id
 * @property string $trade_no
 * @property string $amount
 * @property string $handle_message
 * @property string $creation_time
 * @property string $handle_time
 * @property int $status
 */
class MerchantWithdraw extends Model
{

}