<?php
/**
 *  订单模型
 * User: zyy
 * Date: 2017/7/14
 * Time: 9:59
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    const TABLE_NAME = 'orders';
    protected $table = self::TABLE_NAME;
    protected $guarded = ['deleted_at'];
    private $map = [
        'order_num' => 'order_sn',
        'payMoney' => 'price'
    ];


    public function orderRule()
    {
        $map = array_flip($this->map);
        return [
            $map['order_sn'] => 'required|string',
            'buyer_id' => 'required|integer',
            'seller_id' => 'required|integer',
            'amount' => 'required|numeric',
            $map['price'] => 'required|numeric',
            'order_status' =>
                'required|in:cacel,wait,pay,confim,confim_fail,shipments,receive,complate',
            'pay_type' => 'required|in:online,offline,wechat,unionpay,alipay',
            'refund' => 'required|in:none,wait,reject,complate'
        ];
    }

    public $status = array(
        'cacel' => '已取消',
        'wait' => '待付款',
        'pay' => '已付款待后台确认',
        'confim' => '后台已确认成功，待发货',
        'confim_fail' => '后台确认失败',
        'shipments' => '已发货',
        'receive' => '已收货',
        'complate' => '已完成'
    );
    public $payType = array(
        'online' => '线上',
        'offline' => '线下',
        'wechat' => '微信',
        'unionpay' => '银联',
        'alipay' => '支付宝',
    );
    public $refund = array(
        'none' => '不退款',
        'wait' => '待退款',
        'succ' => '同意退款',
        'reject' => '拒绝退款',
        'complate' => '已退款'
    );

    /**订单扩展信息**/
    public function extend()
    {
        return $this->hasOne('App\Models\OrderExtend', 'order_id');
    }
    /**门店信息**/
    public function seller()
    {
        return $this->belongsTo('App\Models\Seller','seller_id','id');
    }
    public function order_goods()
    {
        return $this->hasMany('App\Models\OrderGoods','order_id','id');
    }

    /**字段映射**/
    public function mapData($data)
    {
        $addData = array();
        $map = array_flip($this->map);
        foreach ($data as $key => $val) {
            if (in_array($key, $map)) {
                $addData[$map[$val]] = $val;
                unset($data[$val]);
            } else {
                $addData[$key] = $val;
            }
        }
        return $addData;
    }

    static public function orderNumBulid($uid, $seller_id)
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) .$seller_id.date('d').substr(time(), -4) .$uid.substr(microtime(), 2, 3) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }


}