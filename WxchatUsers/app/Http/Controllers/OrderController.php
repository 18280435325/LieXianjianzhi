<?php
/**
 * 用户订单控制器.
 * User: zyy
 * Date: 2017/9/13
 * Time: 13:27
 */

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use EasyWeChat\Payment\Order;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Common\Code;

class OrderController extends Controller
{
    private $service;
    public function __construct(Request $request,OrderService $orderService)
    {
        parent::__construct($request);
        $this->service = $orderService;
    }

    /** 订单列表
     * @return array
     */
    public function orderList()
    {
        try{
            $userInput = $this->getParam('|status','|start[0]','|length[10]');
            $user = $this->user;
            $info = $this->service->userOrder($user['id'],$userInput['status'],$userInput['start'],$userInput['length']);
            return Code::Y($info);
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }

    /**  订单详情
     * @return mixed
     */
    public function orderInfo()
    {
        try{
            $userInput = $this->getParam('order_id');
            if(!$userInput){
                return Code::_500();
            }
            $info = $this->service->orderInfo($userInput['order_id']);
            return Code::Y($info);
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }

    /** 创建订单
     * @return mixed
     */
    public function doCreate()
    {
        try{
            $userInput = $this->getParam('seller_id','sku_info','address_id','|note');
            if(!$userInput){
                return Code::_500();
            }
            $user = $this->user;
            $data = array_merge($userInput,['uid'=>$user['id']]);
            $id = $this->service->doCreate($data);
            return Code::Y(['id'=>$id]);
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }

    /** 取消订单
     * @return mixed
     */
    public function cancelOrder()
    {
        try{
            $userInput = $this->getParam('order_id');
            if(!$userInput){
                return Code::_500();
            }
            $user = $this->user;
            $this->service->cancel($user['id'],$userInput['order_id']);
            return Code::Y();
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }




}