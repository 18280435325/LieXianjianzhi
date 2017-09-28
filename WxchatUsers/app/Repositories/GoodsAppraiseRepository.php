<?php
/**
 * 商品评价数据源.
 * User: zyy
 * Date: 2017/9/18
 * Time: 15:59
 */

namespace App\Repositories;
use App\Repositories\BaseRepository;
use App\Models\GoodsAppraise;

class GoodsAppraiseRepository extends BaseRepository
{
    public $filed = [
        'id'=>'id',
        'uid'=>'uid',
        'order_id'=>'order_id',
        'sku'=>'sku',
        'appraise'=>'appraise',
        'star'=>'star',
        'extend'=>'extend',
        'created_at'=>'created_at'
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
        return GoodsAppraise::class;
    }

    /** 新增商品评价
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function create($data)
    {
        $addAppraise = GoodsAppraise::create($data);
        if(!$addAppraise){
            throw new \Exception(config('code._701'));
        }
        return $addAppraise->id;
    }

    //商品评价列表
    public function appraiseList($search)
    {
        $appraise = new GoodsAppraise();
        if(isset($search['ids'])){
            $appraise = $appraise->whereIn('id',explode(',',$search['ids']));
        }
        if(isset($search['seller_id'])){
            $appraise = $appraise->where('seller_id',$search['seller_id']);
        }
        if(isset($search['uid'])){
            $appraise = $appraise->where('uid',$search['uid']);
        }
        if(isset($search['order_id'])){
            $appraise = $appraise->where('order_id',$search['order_id']);
        }
        if(isset($search['sku'])){
            $appraise = $appraise->where('sku',$search['sku']);
        }
        $appraise = $appraise->select(...$this->filed())->get();
        return $appraise;
    }


}

