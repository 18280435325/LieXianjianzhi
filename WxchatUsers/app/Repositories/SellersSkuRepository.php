<?php
/**
 * 门店商品数据源.
 * User: zyy
 * Date: 2017/9/18
 * Time: 15:59
 */

namespace App\Repositories;
use App\Repositories\BaseRepository;
use App\Models\SellersSku;

class SellersSkuRepository extends BaseRepository
{
    public $filed = [
        'id'=>'id',
        'seller_id'=>'seller_id',
        'sku_id'=>'sku_id',
        'price'=>'price',
        'amount'=>'amount',
        'status'=>'status',
        'created_at'=>'created_at',
        'cate_id'=>'cate_id'
    ];

    public function filed($table='')
    {
        $selectFiled = [];
        foreach ($this->filed as $key => $val) {
            if($table){
                $selectFiled [] = $table.$key . ' as ' . $val;
            }else{
                $selectFiled [] = $key . ' as ' . $val;
            }

        }
        return $selectFiled;

    }
    public function model()
    {
        return SellersSku::class;
    }

    /** 获取门店商品记录
     * @param $search
     * @param $count
     * @param $length
     * @return mixed
     */
    public function getSellersSku($search,$count,$length)
    {
        $SellersSku = new $this->model;
        if(isset($search['seller_id'])){
            $SellersSku = $SellersSku->where('seller_id',$search['seller_id']);
        }
        if(isset($search['category_id'])){  //该分类ID集合是服务端处理过的含子级分类的集合
            $SellersSku = $SellersSku->whereIn('cate_id',$search['category_id']);
        }
        if(isset($search['goods_name'])){
            $goods_name = $search['goods_name'];
            $SellersSku = $SellersSku->where('goods_name','like',"%$goods_name%");
        }
        //--【勿删】该返回：是以商品服务获取商品信息，未使用冗余字段的方法，和备份文件SellerSkuService_beiFen 使用
//        --$SellersSku = $SellersSku->select(...$this->filed())->offset($count)->limit($length)->get()->toArray();
//        --return $SellersSku;
        //以商品ID分组，获取达到所需商品的数量
            $list = $SellersSku->groupBy('goods_id')->select(app('db')->raw("id,goods_id,goods_name,
                        GROUP_CONCAT(sku_id) AS sku_id,GROUP_CONCAT(price) AS price,GROUP_CONCAT(amount) AS amount"))
                           ->offset($count)->limit($length)->get()->toArray();
            $count =$SellersSku->groupBy('goods_id')->count('id');
        return compact('list','count');
    }



}

