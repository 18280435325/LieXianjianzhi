<?php
/**
 * 订单日志模型.
 * User: zyy
 * Date: 2017/9/26
 * Time: 13:38
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrdersLog extends Model
{
    const TABLE_NAME   = 'orders_log';
    protected $table   = self::TABLE_NAME;
    protected $guarded = ['deleted_at'];

}

