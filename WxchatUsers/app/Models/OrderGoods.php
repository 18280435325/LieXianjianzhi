<?php
/**
 * Created by PhpStorm.
 * User: zyy
 * Date: 2017/7/14
 * Time: 11:28
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrderGoods extends Model
{
    const TABLE_NAME = 'orders_goods';
    protected $table = self::TABLE_NAME;
    protected $guarded = ['deleted_at'];
}