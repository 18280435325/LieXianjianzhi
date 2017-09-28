<?php
/**
 * Created by PhpStorm.
 * User: zyy
 * Date: 2017/7/14
 * Time: 17:13
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrderExtend extends Model
{
    const TABLE_NAME = 'orders_extend';
    protected $table = self::TABLE_NAME;

}