<?php
/**
 * 商品控制器.
 * User: zyy
 * Date: 2017/9/20
 * Time: 10:48
 */

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Services\SellerSkuService;
use Illuminate\Http\Request;
use App\Common\Code;
class GoodsController extends Controller
{
    private $service;

    public function  __construct(Request $request,SellerSkuService $sellerSkuService)
    {
        parent::__construct($request);
        $this->service = $sellerSkuService;
    }

    /** 获取门店商品分类列表
     * @return mixed
     */
    public function getMenu()
    {
        try{
            $userInput = $this->getParam('seller_id');
            if(!$userInput){
                return Code::_500();
            }
            $menu         = $this->service->getMenu($userInput['seller_id']);
            $defaultGoods = $this->service->getDefaultGood($userInput['seller_id']);
            return Code::Y(['menu'=>$menu,'default_goods_list'=>$defaultGoods]);
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }

    /** 获取分类商品列表
     * @return mixed
     */
    public function getMenuGoods()
    {
        try{
            $userInput = $this->getParam('seller_id','cate_id','|start[0]','|length[25]');
            if(!$userInput){
                return Code::_500();
            }
            $goodsList = $this->service->getGoodsListByCate($userInput['seller_id'],$userInput['cate_id'],$userInput['start'],$userInput['length']);
            return Code::Y($goodsList);
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }

    /** 搜索商品
     * @return mixed
     */
    public function searchGoods()
    {
        try{
            $userInput = $this->getParam('seller_id','name','|start[0]','|length[25]');
            if(!$userInput){
                return Code::_500();
            }
            $info =  $this->service->searchGoods($userInput['seller_id'],$userInput['name'],$userInput['start'],$userInput['length']);
            return Code::Y($info);
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }

    }

    /** 获取商品详情
     * @return mixed
     */
    public function getSkuInfo()
    {
        try{
            $userInput = $this->getParam('seller_id','sku_id');
            if(!$userInput){
                return Code::_500();
            }
            $info = $this->service->getInfo($userInput['seller_id'],$userInput['sku_id']);
            return Code::Y($info);
        }catch (\Exception $e){
            return Code::_400($e->getMessage());
        }
    }
}