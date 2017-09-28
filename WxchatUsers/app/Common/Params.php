<?php
/**
 * Created by PhpStorm.
 * User: diamonds.a
 * Date: 2017/6/7
 * Time: 下午1:19
 */

namespace App\Common;


trait Params
{

    /**
     * 获取参数
     * @param array $arg  要获取的参数
     * @param bool $post  是否是post请求
     * @param bool $ajax  是否为ajax
     * @return array|bool
     *  arg参数规则 eg:
     *  ['name','pwd','|tel','|email[aaa@test.com]','|time[:date('Y-m-d H:i:s')]']
     *  默认为必要参数，‘|’表示为不是必要的参数，‘[]’为当该参数不存在时赋予其默认值，默认值当中用“:”使用函数返回值作为默认值’
     */
    public function getParam(...$arg)
    {
        $request = $this->request;
        $data= $request->all();
        $map=[];
        foreach ($arg as $val){
            //根据|检测当前是必需参数还是可选参数
            if(strpos($val,'|') !==false){
                $val=ltrim($val,' |');
                $at=strpos($val,'[');  //实际键
                $key = $at ===false?$val:substr($val,0,$at);
                if(isset($data[$key])){
                    $map[$key] = is_string($data[$key])?trim($data[$key]):$data[$key];
                }else{
                    //获取无参数时默认值
                    $preg ='/\w+?\[(\S*)?\]$/';
                    $bstop=preg_match($preg,$val,$default);
                    if(!$bstop){
                        $map[$key]='';
                        continue;
                    }
                    $child_str = $default[1];
                    if( strpos($child_str,':') !==false){
                        //默认值函数赋值
                        $fun_val= ltrim($child_str,':');
                        $index = strpos($child_str,'(');
                        $fun = rtrim(substr($fun_val,0,$index),'()');
                        $parm =substr($fun_val,$index,-1);
                        $parm =explode(',',$parm);
                        $map[$key]=call_user_func_array($fun,$parm);
                    }else{
                        $map[$key] = $child_str;
                    }
                }
                continue;
            }
            if(!isset($data[$val]) || (is_string($data[$val]) && trim($data[$val]) ==='')){
                return false;
            }
            $map[$val]=is_string($data[$val])?trim($data[$val]):$data[$val];
        }
        return $map;
    }
}