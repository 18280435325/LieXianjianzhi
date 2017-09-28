<?php
/**
 * 验证码服务.
 * User: zyy
 * Date: 2017/9/12
 * Time: 17:00
 */

namespace App\Services;

use App\Models\SmsLog;
use App\Models\SmsQueue;

class SmsService
{
    public function checkSms($phone,$checkYzm)
    {
        $yzm = SmsLog::where('phone',$phone)->orderBy('created_at','desc')->first();
        if(!$yzm){
            throw new \Exception(config('code._503'));
        }
        if((time()- strtotime($yzm->created_at))>SmsLog::YZMTIMEOUT){
            throw new \Exception(config('code._504'));
        }
        if($checkYzm!=$yzm->captcha){
            throw new \Exception(config('code._504'));
        }
        return true;
    }
}