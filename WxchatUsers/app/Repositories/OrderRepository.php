<?php
/**
 * 订单数据源.
 * User: zyy
 * Date: 2017/9/13
 * Time: 13:30
 */
namespace App\Repositories;
use App\Repositories\BaseRepository;
use App\Models\Orders;


class OrderRepository extends BaseRepository
{
    public $filed = [
        'id'=>'id',
        'order_sn' => 'order_sn',
        'buyer_id' => 'buyer_id',
        'seller_id' => 'seller_id',
        'amount' => 'allMoney',
        'price'=>'payMoney',
        'order_status'=>'status',
        'pay_type'=>'pay_type',
        'refund'=>'refund',
        'pay_time'=>'pay_time',
        'created_at'=>'createTime'
    ];

    /**
     * 查询字段
     * @return array
     */
    public function filed($table='')
    {
        $selectFiled = [];
        foreach ($this->filed as $key => $val) {
            if($table){
                $selectFiled [] = Orders::TABLE_NAME.$key . ' as ' . $val;
            }else{
                $selectFiled [] = $key . ' as ' . $val;
            }

        }
        return $selectFiled;
    }

    public function model()
    {
        return Orders::class;
    }

    /** 订单列表
     * @param $search [搜索条件]
     * @return mixed
     */
    public function orderList($search,$start,$length)
    {
        $order = new $this->model;
        if(isset($search['buyer'])){
            $order = $order->where('buyer_id',$search['buyer']);
        }
        if(isset($search['status'])){
            $order = $order->where('order_status',$search['status']);
        }
        $count = $order->count();
        $order = $order->with('seller')->select(...$this->filed())
               ->offset($start)->limit($length)->get()->toArray();
        return compact('count','order');
    }

    /** 获取订单详情
     * @param $order_id [订单ID]
     * @return mixed
     */
    public function orderInfo($order_id)
    {
        $orderInfo = new $this->model;
        $orderInfo = $orderInfo->with(['extend','seller','order_goods'])->where('id',$order_id)
                     ->select(...$this->filed())->first();
        return $orderInfo;
    }

    /** 创建订单
     * @param $data
     * @throws \Exception
     */
    public function doCreate($data)
    {
        $order = new $this->model;
        $order_sn = $order->orderNumBulid($data['seller_id'],$data['seller_id']);
        $data = array_merge($data,['order_sn'=>$order_sn,'order_status'=>'wait','pay_type'=>'online','refund'=>'none']);
        $addOrder = $order->create($data);
        if(!$addOrder){
            throw new \Exception(config('code._800'));
        }
        return $addOrder;
    }





}