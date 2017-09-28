<?php
/**
 * 用户控制器.
 * User: zyy
 * Date: 2017/9/11
 * Time: 13:53
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Common\Code;
use App\Services\UserService;
use App\Services\SmsService;
use App\Common\MyEasyWeChat;

class UserController extends Controller
{
    use MyEasyWeChat;
    private $service;
    private $yzmService;
    public function __construct(Request $request,UserService $service,SmsService $smsService)
    {
        parent::__construct($request);
        $this->service = $service;
        $this->yzmService = $smsService;
    }

    /** 用户登录
     * @return mixed
     */
    public function UserLogin()
    {
        try{
            $userInput = $this->getParam('phone','pwd');
            if(!$userInput){
                return Code::_500();
            }
            $info = $this->service->userLogin($userInput);
            return Code::Y($info);
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }

    /** 获取区域列表
     * @param //area_id 父级区域id
     * @return mixed
     */
    public function areaList(){
        try{
            $userInput = $this->getParam('|area_id[23]');
            $cityList = app('db')->table('areas')->where('parent_id',$userInput['area_id'])->select('id','name','deep')->get()->toArray();
            return Code::Y(array('list'=>$cityList));
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }


    /** 用户注册
     * @return mixed
     */
    public function userRegister()
    {
        try{
            $userInput = $this->getParam('name','pwd','phone','|city_id','|area_id','|address','wxToken');
            if(!$userInput){
                return Code::_500();
            }
            $addInfo = $this->service->userRegister($userInput);
            return Code::Y($addInfo);
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }

    /** 修改用户密码
     * @return mixed
     */
    public function updatePwd()
    {
        try{
            $userInput = $this->getParam('yzm','new_password');
            if(!$userInput){
                return Code::_500();
            }
            //检验验证码
            $user = $this->user;
            if($this->yzmService->checkSms($user['phone'],$userInput['yzm'])){
                $arg = app('helper')->q(['user_id'=>$user['id'],'pwd'=>$userInput['new_password']]);
                app('api')->user->patch('user/update',$arg);
                return Code::Y();
            }else{
                return Code::_503();
            }
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }

    /** 退出登录
     * @return mixed
     */
    public function outLogin()
    {
        try{
            $userInfo = $this->user;
            $openId   = json_decode( $userInfo['ext'],true)['wxToken'];
            $token = app('redis')->mget($openId);
            app('redis')->del($openId);
            app('redis')->del($token);
            return Code::Y();
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }

    /** 获取用户信息
     * @return mixed
     */
    public function getOne()
    {
        try{
            $userInfo = $this->user;
            $temp = [
                'name'=>$userInfo['name'],
                'city_id'=>$userInfo['city_id'],
                'area_id'=>$userInfo['area_id'],
                'address'=>$userInfo['address']
            ];
            return Code::Y($temp);
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }

    /**更改用户信息
     * @return mixed
     */
    public function updateInfo()
    {
        try{
            $userInput = $this->getParam('|city_id','|area_id','|address');
            $user =$this->user;
            $data['user_id'] =$user['id'];
            if(''!==$userInput['city_id']){
                $data['city_id'] = $userInput['city_id'];
            }
            if(''!==$userInput['area_id']){
                $data['area_id'] = $userInput['area_id'];
            }
            if(!empty($userInput['address'])){
                $data['address'] = $userInput['address'];
            }
            $arg = app('helper')->q($data);
            app('api')->user->patch('user/update',$arg);
            return Code::Y();
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }

    /** 更改用户手机号码
     * @return mixed
     */
    public function updatePhone()
    {
        try{
            $userInput = $this->getParam('yzm','new_phone');
            if(!$userInput){
                return Code::_500();
            }
            //检验验证码
            $user = $this->user;
            if($this->yzmService->checkSms($user['phone'],$userInput['yzm'])){
                $arg = app('helper')->q(['user_id'=>$user['id'],'phone'=>$userInput['new_phone']]);
                app('api')->user->patch('user/update',$arg);
                return Code::Y();
            }else{
                return Code::_503();
            }
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }

    public function oAuthLogin()
    {
        $Ewx = $this->getEasyWeChatObj();
     //   $userService = $Ewx->user;
        dd($Ewx->oauth->user());

    }

}