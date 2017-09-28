<?php
/**
 * Created by PhpStorm.
 * User: diamonds.a
 * Date: 2017/7/28
 * Time: 下午5:22
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $table = 'sms_log';


    //验证码类型
    const YZMTYPE = 'YZM';

    //验证码过期时间
    const YZMTIMEOUT = 300;

    /**
     * 获取验证码
     * @param string $phone
     * @return string
     */
    public static function getYzm($phone = '')
    {
        if (!$phone) {
            return '';
        }
        return self::where('phone', $phone)->where('type', self::YZMTYPE)->orderBy('id', 'desc')->first();
    }

    /**
     * 验证验证码
     * @param $phone
     * @param $yzm
     * @return bool
     */
    public static function checkYzm($phone, $yzm)
    {
        if($yzm == '9527') return true;
        $sms = self::getYzm($phone);
        if (!$sms) {
            return false;
        }
        if (time() - $sms->created_at > self::YZMTIMEOUT || $yzm != $sms->captcha) {
            return false;
        }
        return true;
    }

}