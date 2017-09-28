<?php
/**
 * 发送验证码.
 * User: diamonds.a
 * Date: 2017/7/28
 * Time: 下午6:10
 */

namespace App\Http\Controllers;


use App\Common\Code;
use App\Models\SmsLog;
use App\Models\SmsQueue;
use Illuminate\Http\Request;

class SmsController extends Controller
{

    /**
     * 发送验证码
     * @param Request $request
     * @return mixed
     */
    public function send(Request $request)
    {
        $phone = $request->input('phone');
        if (!$phone) {
            return Code::_500();
        }
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= random_int(0, 9);
        }
        $msg = "验证码：{$code}";
        $data = [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'phone' => $phone,
            'captcha' => $code,
            'content' => $msg,
            'type' => SmsLog::YZMTYPE
        ];
        $bool = SmsQueue::create($data);
        if(!$bool){
            return Code::_502();
        }
        return  Code::Y();
    }
}