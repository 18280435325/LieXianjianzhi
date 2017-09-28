<?php
/**
 * Created by PhpStorm.
 * User: diamonds.a
 * Date: 2017/6/7
 * Time: 下午6:21
 */

namespace App\Common;


use App\Http\Response;

class Code
{
    /**
     * 返回函数
     * @param $name
     * @param $arguments
     * @return static
     */
    public static function __callStatic($name, $arguments)
    {
//        dd($name);
        $arg = isset($arguments[0])?$arguments[0]:[];
        if ($name == 'Y') {
            return Response::success($arg);
        } else {
            $msg = 'ERROR';
            $data = [];
            if ($r = config('code.' . $name)) {
                $msg = $r;
            }
            if (is_string($arg)) {
                $msg = $arg;
            } else {
                $data = $arg;
            }
            return Response::errors(array_merge(['code' => substr($name, 1), 'message' => $msg],$data));
        }
    }

    /**
     * 获取错误信息
     * @param int $code
     * @return mixed|string
     */
    public static function getMsg($code=0){
        if(!$code) return '';
        return config('code._'.$code);
    }
}