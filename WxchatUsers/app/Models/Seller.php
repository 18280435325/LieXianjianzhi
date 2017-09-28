<?php
/**
 * Created by PhpStorm.
 * User: zyy
 * Date: 2017/7/6
 * Time: 10:52
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Seller extends Model
{
    use SoftDeletes;
    const TABLE_NAME = 'sellers';
    protected $table = self::TABLE_NAME;
    protected $guarded = ['deleted_at'];

}