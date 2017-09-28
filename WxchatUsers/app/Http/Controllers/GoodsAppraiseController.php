<?php
/**
 * 商品评价控制器.
 * User: zyy
 * Date: 2017/9/18
 * Time: 16:37
 */

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Common\Code;
use Illuminate\Http\Request;
use App\Services\GoodsAppraiseService as service;

class GoodsAppraiseController extends Controller
{
    private $service;
    public function __construct(Request $request,service $services)
    {
        parent::__construct($request);
        $this->service = $services;
    }

    /** 新增商品评价
     * @return mixed
     */
    public function addAppraise()
    {
        try{
          $userInput = $this->getParam('order_id','seller_id','sku','appraise','|star','|extend');
            if(!$userInput){
                return Code::_500();
            }
          $user = $this->user;
          if(!empty($userInput['extend'])){
              $userInput['extend'] = is_array($userInput['extend'])?json_encode($userInput['extend']):$userInput['extend'];
          }else{
              $userInput['extend'] = json_encode([]);
          }
          $addData = array_merge($userInput,['uid'=>$user['id']]);
          $appraiseId = $this->service->doCreate($addData);
          return Code::Y(['id'=>$appraiseId]);
        }catch (\Exception $e){
          return Code::_400($e->getMessage());
        }
    }


}