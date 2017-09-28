<?php
/**
 * 收货地址模型.
 * User: zyy
 * Date: 2017/9/19
 * Time: 10:40
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class DeliveryAddress extends Model
{
    use SoftDeletes;
    const TABLE_NAME   = 'delivery_address';
    const MAX_COUNT    = 6;              //每个用户最多存储收货地址的个数
    protected $table   = self::TABLE_NAME;
    protected $guarded = ['deleted_at'];
    protected $dates   = ['deleted_at'];

}
