<?php
/**
 * 收货地址管理控制器.
 * User: zyy
 * Date: 2017/9/19
 * Time: 10:36
 */

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Common\Code;
use App\Services\AddressService;

class AddressController extends Controller
{
    private $service;
    public function __construct(Request $request,AddressService $addressService){
        parent::__construct($request);
        $this->service = $addressService;
    }

    /** 新增收货地址
     * @return mixed
     */
    public function doCreate()
    {
        try{
            $userInput = $this->getParam('delivery_user','phone','address','city_id','area_id','|default[0]');
            if(!$userInput){
                return Code::_500();
            }
            $user  = $this->user;
            $data  = array_merge($userInput,['uid'=>$user['id']]);
            $info  = $this->service->addAddress($data);
            return Code::Y(['id'=>$info]);
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }

    /** 修改收货地址
     * @return mixed
     */
    public function doUpdate()
    {
        try{
            $userInput = $this->getParam('id','|delivery_user','|phone','|address','|city_id','|area_id');
            if(!$userInput){
                return Code::_500();
            }
            $this->service->doUpdate($userInput);
            return Code::Y();
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }

    /**  删除收货地址
     * @return mixed
     */
    public function doDelete()
    {
        try{
            $userInput = $this->getParam('id');
            $user = $this->user;
            if(!$userInput){
                return Code::_500();
            }
            $this->service->doDelete($user['id'],$userInput['id']);
            return Code::Y();
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }

    //获取收货地址列表
    public function getList()
    {
        $user = $this->user;
        $list = $this->service->getList($user['id']);
        return Code::Y($list);
    }

    //设置默认收货地址
    public function setDefault()
    {
        try{
            $userInput = $this->getParam('address_id');
            if(!$userInput){
                return Code::_500();
            }
            $user = $this->user;
            $this->service->doDefault($user['id'],$userInput['address_id']);
            return Code::Y();
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }

    }
}
