<?php
/**
 * 订单服务.
 * User: zyy
 * Date: 2017/9/13
 * Time: 13:53
 */

namespace App\Services;
use App\Models\OrderExtend;
use App\Models\OrderGoods;
use App\Models\OrdersLog;
use App\Models\Seller;
use App\Repositories\OrderRepository;
use App\Models\Orders;
use App\Models\SellersSku;
use App\Models\DeliveryAddress;



class OrderService
{
    private $orderRep;
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRep = $orderRepository;
    }

    /** 用户订单列表
     * @param $uid [用户ID]
     * @param string $status [订单状态]
     * @param $start [起始条数]
     * @param $length [所需条数]
     * @return array
     */
    public function userOrder($uid,$status='',$start,$length)
    {
        $search['buyer'] = $uid;
        if(''!==$status){
            $search['status']=$status;
        }
        $info = $this->orderRep->orderList($search,$start,$length);
        $list  = $info['order'];
        $count = $info['count'];
        $temp = [];
        $orderObj = new Orders();
        foreach($list as $key=>$val){
            $temp[$key] = [
                'id'=> $val['id'],
                'order_sn'=>$val['order_sn'],
                'allMoney'=>$val['allMoney'],
                'payMoney'=>$val['payMoney'],
                'status'=>$orderObj->status[$val['status']],
                'pay_type'=>$orderObj->payType[$val['pay_type']],
                'refund'=>$orderObj->refund[$val['refund']],
                'createTime'=>$val['createTime'],
                'pay_time'=>$val['pay_time'],
                'seller'=>$val['seller']['name']
            ];
        }
        return ['list'=>$temp,'count'=>$count];
    }

    /** 订单详细
     * @param $order_id [订单详情]
     * @return array
     */
    public function orderInfo($order_id)
    {
        $info = $this->orderRep->orderInfo($order_id);
        if($info){
            $info = $info->toArray();
        }else{
            return [];
        }
        $orderObj = new Orders();
        //获取商品相关信息
        $goods = [];
        foreach($info['order_goods'] as $gKey=>$gVal){
            $goodsInfo = json_decode($gVal['goods_info'],true);
            $goods[$gKey]=[
                'name'=>$goodsInfo['goods_name'],
                'price'=>$gVal['price'],
                'amount'=>$gVal['amount'],
                'num'=>$gVal['num'],
                'unit'=>$gVal['unit'],
                'fav'=>$gVal['fav'],
                'img'=>$goodsInfo['image']
            ];
        }

        $imgHashs =getImagesUrl(array_column($goods,'img'));
        foreach($goods as $gKey=>$gVal){
            $imgHash = $gVal['img'];
            $goods[$gKey]['img'] = $imgHashs[$imgHash] ;
        }
        $temp = [
            'order_sn' => $info['order_sn'],   //订单号
            'seller'  =>  $info['extend']['seller'], //门店名称
            'delivery_address' => $info['extend']['shipping_address']." ". $info['extend']['buyer']." ". $info['extend']['shipping_phone'], //收货地址
            'buyer'=> $info['extend']['buyer'], //买家名称
            'note'=>$info['extend']['note'],    //买家备注
            'status'=>$orderObj->status[$info['status']], //订单状态
            'pay_type'=>$orderObj->payType[$info['pay_type']], //支付方式
            'delivery_person'=>$info['extend']['delivery_person'],  //配送人员
            'delivery_phone'=>$info['extend']['delivery_phone'],   //配送人员电话
            'refund'=>$orderObj->refund[$info['refund']],  //退款状态
            'createTime'=>$info['createTime'],  //订单创建时间
            'pay_time'=>$info['pay_time'],   //支付时间
            'buy_goods'=>$goods   //购买商品信息
        ];
        return $temp;
    }

    /** 创建订单
     * @param $data
     * @throws \Exception
     */
    public function doCreate($data)
    {
        $db = app('db');
        $data = is_array($data)?$data:json_decode($data,true);
        $buySkuInfo = $data['sku_info'];
        $buyNum = [];   //购买数量
        $skuIds = [];   //所有sku的ID
        foreach($buySkuInfo as $key=>$val){
            $buyNum[$val['id']]=$val['num'];
            $skuIds[] = $val['id'];
        }
        $serviceSku = getSkuInfo($skuIds);    //SKU信息
        $sellerSku = SellersSku::where('seller_id',$data['seller_id'])->whereIn('sku_id',$skuIds)
            ->select('sku_id','price','amount')->get()->toArray();         //当前门店中SKU信息
       if(count($sellerSku)!=count($serviceSku)){
           throw new \Exception(config('code._801'));
       }
        $price = [];//售价
        foreach($sellerSku as $val){
            $price[$val['sku_id']]=$val['price'];
        }
        $orderSku = [];  //订单SKU商品详情信息
        $allMoney = 0;   //订单总金额
        foreach($serviceSku as $key=>$val){
            $saveHistoryData = [     //存储当前采购商品的信息到DB的goods_info字段
                'goods_id'=>$val['goods_id'],
                'goods_name'=>$val['goods_name'],
                'barcode'=>$val['barcode'],
                'wholease_price'=>$val['wholease_price'],
                'image'=>$val['image'],
                'cate_id'=>$val['cate_id'],
                'brand_id'=>$val['brand_id'],
            ];
            $allUnit = $val['unit'];
            array_multisort(array_column($allUnit,'default'),SORT_DESC,$allUnit);
            $defaultUnit = $allUnit[0]['name'];
            $orderSku[$key] = [
                'order_id'=>null,
                'sku_id'=>$val['id'],
                'price'=>$price[$val['id']],
                'amount'=>$buyNum[$val['id']]*$price[$val['id']],
                'num'=>$buyNum[$val['id']],
                'unit'=>$defaultUnit,
                'fav'=>'',
                'created_at'=>date('Y-m-d H:i:s'),
                'goods_info'=>json_encode($saveHistoryData)
            ];
            $allMoney+=$buyNum[$val['id']]*$price[$val['id']];
        }

        $db->beginTransaction();
        try{
            //创建订单
            $addData = [
                'buyer_id'=>$data['uid'],
                'seller_id'=>$data['seller_id'],
                'amount'=>$allMoney,
                'price'=>$allMoney
            ];
           $addOrder = $this->orderRep->doCreate($addData);
            //添加订单详情
             //获取收货地址信息
            $addressInfo = DeliveryAddress::find($data['address_id']);
            $addExtendData = [
                'order_id'=>$addOrder->id,
                'order_sn'=>$addOrder->order_sn,
                'buyer'=>$addressInfo->delivery_user,
                'seller'=>Seller::where('id',$data['seller_id'])->value('name'),
                'fav'=>'',
                'city_id'=>$addressInfo->city_id,
                'area_id'=>$addressInfo->area_id,
                'shipping_address'=>$addressInfo->address,
                'shipping_phone'=>$addressInfo->phone,
                'delivery_person'=>'',
                'delivery_phone'=>'',
                'note'=>$data['note'],
                'created_at'=>date('Y-m-d H:i:s')
            ];
            OrderExtend::insert($addExtendData);
            //添加订单商品
            foreach($orderSku as $key=>$val){
                $orderSku[$key]['order_id'] = $addOrder->id;
            }
            OrderGoods::insert($orderSku);
            //添加订单日志
            $addLogData['order_id'] = $addOrder->id;
            $addLogData['before_status']='wait';
            $addLogData['after_status']='wait';
            $addLogData['remark']='创建了新的订单';
            OrdersLog::create($addLogData);
            $db->commit();
            return $addOrder->id;
        }catch (\Exception $e){
            $db->rollBack();
            throw new \Exception($e->getMessage());
        }

    }

    public function cancel($user_id,$order_id)
    {
        $order = Orders::find($order_id);
        if(!$order){
            throw new \Exception(config('code._802'));
        }
        if($order->buyer_id!=$user_id){
            throw new \Exception(config('code._802'));
        }
        if($order->order_status!='wait'){
            throw new \Exception(config('code._803'));
        }
        $order->order_status='cacel';
        if(!$order->save()){
            throw new \Exception(config('code._804'));
        }
        //添加订单日志
        $addLogData['order_id'] = $order_id;
        $addLogData['before_status']='wait';
        $addLogData['after_status']=$order->order_status;
        $addLogData['remark']='取消了订单';
        OrdersLog::create($addLogData);
        return true;
    }
    public function oAuthLogin()
    {

    }

}