<?php
/**
 * 商品服务备份文件 未使用冗余字段 但未分页.
 * User: zyy
 * Date: 2017/9/20
 * Time: 11:03
 */

//namespace App\Services;
use App\Models\OrderGoods;
use App\Models\SellersSku;
use App\Repositories\SellersSkuRepository;
use App\Models\Orders;


class SellerSkuService
{
    private $repository;
    public function __construct(SellersSkuRepository $repository)
    {
        $this->repository = $repository;
    }


    /** 获取门店的菜单
     * @param $seller_id [门店ID]
     * @return array
     */
    public function getMenu($seller_id)
    {
        $redis = app('redis');
        //从缓存中查找
        $cacheMenu = $redis->mget(config('config.menu_redisPrefix').$seller_id);
        if(array_filter($cacheMenu)){
            return json_decode($cacheMenu[0],true);
        }

        $sellerCate = SellersSku::where('seller_id', $seller_id)->distinct()->pluck('cate_id')->toArray();
        //获取所有分类
        $allCate = app('api')->goods->get('cate/lists');
        //一级分类
        $leave01 = [];
        //二级及以下分类
        $leave_gt_02 = [];
        //按id为键组装的总分类数组
        $allSortCate=[];
        //所有一级分类
        $allLeave01Cate = [];
        foreach($allCate as $key=>$val){
            $allSortCate[$val['id']]=$val;
            if(in_array($val['id'],$sellerCate)){
                if($val['pid']==0){
                    $leave01[$val['id']] = $val;
                }else{
                    $leave_gt_02[]=$val;
                }
            }
            if($val['pid']==0){
                $allLeave01Cate[] = $val;
            }
        }
        //只要二级分类，剔除二级以下的分类
        $leave02 = [];
        foreach($leave_gt_02 as $key=>$val){
            if( $allSortCate[$val['pid']]['pid']==0){
                $leave02[]=$val;
            }
        }

        //查找二级分类中的父级分类
        $allLv01Id = array_column($allLeave01Cate,'id');
        foreach($leave02 as $key=>$val){
            if(in_array($val['pid'],$allLv01Id)){
                $leave01[$allSortCate[$val['pid']]['id']] = $allSortCate[$val['pid']];
            }
        }
        //组装分类树
        foreach($leave01 as $key=>$val){
            foreach($leave02 as $k=>$v){
                if($v['pid']==$val['id']){
                    $leave01[$key]['children'][]=$v;
                }
            }
            if(!isset($leave01[$key]['children'])){   //没有子级
                $leave01[$key]['children']=[];
            }
        }
        //缓存门店菜单
        $redis->setex(config('config.menu_redisPrefix').$seller_id,(env('SKU_CACHE_TIME',86400))/8,json_encode(array_values($leave01)));
        return array_values($leave01);

    }


    /** 获取分类列表并返回默认商品列表
     * @param $seller_id [门店ID]
     * @return array
     */
    public function getDefaultGood($seller_id){

        $menu = $this->getMenu($seller_id);
        //获取默认第一个分类的商品（如果含二级分类，取二级分类的第一个）
        if(!$menu){
            return [
                'menu_id'=>null,
                'default_goods'=>[]
            ];
        }
        $firstMenu = $menu[0]['id'];
        if(count($menu[0]['children'])){
            $firstMenu = $menu[0]['children'][0]['id'];
        }
        $goodsList = $this->getGoodsListByCate($seller_id,$firstMenu,0,25);
        return ['default_cate_id'=>$firstMenu,'goods_list'=>$goodsList];
    }

    /** 通过分类ID获取商品列表
     * @param $seller_id [门店ID]
     * @param $cate_id [分类ID]
     * @return array
     */
    public function getGoodsListByCate($seller_id,$cate_id,$start,$length)
    {
        $allCate = app('api')->goods->get('cate/lists');
        //判断是否拥有二级分类
        $isFirstMenu = false;
        foreach($allCate as $key=>$val){
          if($val['id']==$cate_id){
              if($val['pid']==0){
                  $isFirstMenu = true;
              }
              break;
          }
        }
        $search = [
            'seller_id'=>$seller_id
        ];
        if($isFirstMenu){   //直接获取该分类下的SKU
            $search['category_id'] = [$cate_id];
            $seller_sku = $this->repository->getSellersSku($search,$start,$length);
        }else{    //获取该分类及其子分类的商品
            $childCate = getSubs($allCate,$cate_id);
            if($childCate){
                $childCate_id = array_column($childCate,'id');
            }else{
                $childCate_id = [];
            }
            $search['category_id'] = array_unique(array_merge([$cate_id],$childCate_id));
            $seller_sku = $this->repository->getSellersSku($search,$start,$length);
        }
        if(!$seller_sku){   //不存在商品直接返回
            return [];
        }
        //商品售价
        $price    = [];
        //商品库存
        $sku_save = [];
        foreach($seller_sku as $key=>$val){
           $price[$val['sku_id']]    = $val['price'];
           $sku_save[$val['sku_id']] = $val['amount'];
        }
        //获取商品信息
        $sku_info =getSkuInfo(array_keys($price));
        //获取商品图片
        $imageHashes = getImagesUrl( array_column($sku_info,'image'));
      //计算商品销量
        $order = new Orders();
        $skuSales = $order->join(OrderGoods::TABLE_NAME,Orders::TABLE_NAME.'.id','=',OrderGoods::TABLE_NAME.'.order_id')
                    ->where(Orders::TABLE_NAME.'.seller_id',$seller_id)
                    ->where(Orders::TABLE_NAME.'.order_status','complate')
                    ->whereIn(OrderGoods::TABLE_NAME.'.sku_id',array_keys($price))
                    ->groupBy(OrderGoods::TABLE_NAME.'.sku_id')
                    ->select(app('db')->raw(OrderGoods::TABLE_NAME.'.sku_id,'.' count('.OrderGoods::TABLE_NAME.'.sku_id'.") as sales "))
                    ->get()->toArray();
        //销量
        $sale = [];
        foreach($skuSales as $val){
            $sale[$val['sku_id']] = $val['sales'];
        }
        //组装商品信息
        $goods_list = [];
        foreach($sku_info as $key=>$val){
            $goods_list[$val['goods_id']][] = [
              'goods_id'=>$val['goods_id'],
              'sku_id'=>$val['id'],
              'sku_name'=>$val['goods_name'],
              'image'=>$imageHashes[$val['image']],
              'price'=>$price[$val['id']],
              'amount'=>$sku_save[$val['id']],
              'sale'=>isset($sale[$val['id']])?$sale[$val['id']]:0
            ];
        }
        $goodsId = array_keys($goods_list);
        //获取主商品信息
        $goodsInfo = getGoodsInfo($goodsId);
        //主商品图片Hash
        $goodsHash = [];

        foreach($goodsInfo as $key=>$val){
            $image = explode(',',$val['image']);
            $goodsHash[$val['goods_id']] = isset($image[0])?$image[0]:'';
        }
        $mainGoodsImg = getImagesUrl($goodsHash);
        //添加主商品信息
        $info = [];
        foreach($goodsInfo as $key=>$val){
            $info[$key]['goods_id'] = $val['goods_id'];
            $info[$key]['name']     = $val['goods_name'];
            $info[$key]['notice_time'] = $val['warning'];
            $image = explode(',',$val['image']);
            if(isset($image[0])){
                $imgUrl = $mainGoodsImg[$image[0]];
            }else{
                $imgUrl = config('config.goods_redisPrefix');
            }
            $info[$key]['img'] =  $imgUrl;
            $info[$key]['sku'] = $goods_list[$val['goods_id']];
        }
        return array_values($info);
    }



}