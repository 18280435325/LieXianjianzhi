<?php
/**
 * Created by PhpStorm.
 * User: diamonds.a
 * Date: 2017/7/28
 * Time: 下午6:13
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SmsQueue extends Model
{
    protected $table = 'sms_queue';
    protected $guarded = [];
}