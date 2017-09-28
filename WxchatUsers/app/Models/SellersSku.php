<?php
/**
 * Created by PhpStorm.
 * User: zyy
 * Date: 2017/9/20
 * Time: 13:25
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SellersSku extends Model
{
    const TABLE_NAME   = 'sellers_sku';
    protected $table   = self::TABLE_NAME;
    protected $guarded = ['deleted_at'];
}