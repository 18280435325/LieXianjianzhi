<?php
/**
 * 商品评价模型.
 * User: zyy
 * Date: 2017/9/18
 * Time: 15:39
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsAppraise extends Model
{
    use SoftDeletes;
    const TABLE_NAME = 'goods_appraise';
    protected $table   = self::TABLE_NAME;
    protected $guarded = ['deleted_at'];
    protected $dates   = ['deleted_at'];

}