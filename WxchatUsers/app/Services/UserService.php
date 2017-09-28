<?php
/**
 * 用户服务.
 * User: zyy
 * Date: 2017/9/11
 * Time: 17:55
 */

namespace App\Services;

class UserService
{

    /** 生成用户登录token
     * @param $uid
     * @return string
     */
    public function buildUserToken($uid)
    {
       return  $uid.md5(microtime().uniqid($uid));
    }

    /** 用户登录
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function userLogin($data)
    {
        $arg      = app('helper')->p($data);
        $userInfo = app('api')->user->post('user/login',$arg);
        $userExt = json_decode($userInfo['ext'],true);
        $openId = null;
        if(isset($userExt['wxToken'])){
            if(!empty($userExt['wxToken'])){
                $openId = $userExt['wxToken'];
            }
        }
        if(!$openId){
            throw new \Exception(config('code._600'));
        }
        $uid   = $userInfo['id'];
        $token = $this->buildUserToken($uid);
        $redis = app('redis');
        $jsonUserInfo = json_encode($userInfo);
        //删除用户可能拥有的token
        $possibleToken = $redis->mget($openId);
        if($possibleToken){
            $redis->del($possibleToken);
        }
        $redis->setex($token,24*60*60*env('TOKEN_TIMEOUT'),$jsonUserInfo);
        $redis->set($openId,$token);
        return ['phone'=>$userInfo['phone'],'token'=>$token];
    }


    /** 用户注册
     * @param $data
     * @return mixed
     */
    public function userRegister($data)
    {
        $data     = array_merge($data,['ext'=>['wxToken'=>$data['wxToken']]]);
        $arg      = app('helper')->p($data);
        $addInfo  = app('api')->user->post('user/create',$arg);
        return $addInfo;
    }

}