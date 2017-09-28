<?php
/**
 *  公共函数.
 * User: zyy
 * Date: 2017/6/7
 * Time: 上午11:34
 */

if (!function_exists('request')) {
    function request()
    {
        return new \Illuminate\Http\Request;
    }
}


//多维数组互相组合
function mergeArr($arr)
{
    if (count($arr) >= 2) {
        $one = array_shift($arr);
        $two = array_shift($arr);
        $merge = [];
        foreach ($one as $k => $val) {
            foreach ($two as $v) {
                if (is_array($val)) {
                    $temp = $val;
                    $temp[] = $v;
                    $merge[] = $temp;
                } else {
                    $merge[] = [$val, $v];
                }
            }
        }
        array_unshift($arr, $merge);
        return mergeArr($arr);
    } else {
        return array_shift($arr);
    }
}


/** 根据SKU获取商品信息
 * @param array $sku
 * @param int $isUnit
 * @return array
 */

function getSkuInfo(array $sku, $isUnit = 1)
{
    if (!$sku) {
        return [];
    }
    //SKU 缓存键名前缀
    $redis = app('redis');
    $prefixSku = [];
    foreach ($sku as $key => $val) {
        $prefixSku[] = config('code.sku_redisPrefix') . $val;
    }
    //缓存中的商品
    $redisJsonGoods = $redis->mget($prefixSku);
    $redisGoods = [];
    foreach ($redisJsonGoods as $key => $val) {
        $redisGoods[] = json_decode($val, true);
    }
    //查找缓存中不存在的商品信息
    if (array_filter($redisGoods)) {
        $findSku = array_diff($sku, array_column($redisGoods, 'id'));
    } else {
        $findSku = $sku;
    }
    $allGoods = $redisGoods;
    if ($findSku) {
        $data = [
            'length' => '5000',
            'search' => ['skuid' => $findSku],
            'hasunit' => $isUnit
        ];
        $argc = app('helper')->q($data);
        $info = app('api')->goods->get('sku/lists', $argc);
        if ($info) {
            $info = $info['datas'];
            $allGoods = array_merge((array)$redisGoods, $info);
        }
        //取出的商品信息存入缓存
        foreach ($info as $key => $val) {
            $redis->setex(config('config.sku_redisPrefix') . $val['id'], env('SKU_CACHE_TIME', 86400), json_encode($val));
        }
    }
    return array_filter($allGoods);
}

/** 通过hash集合获取图片URL
 * @param $hashes
 * @return array
 */
function getImagesUrl($hashes)
{
    $hashes = !is_array($hashes) ? $hashes : join(',', $hashes);
    $redis = app('redis');
    $cacheImg = array_filter($redis->mget(explode(',', $hashes)));
    $imagesUrl = [];
    foreach ($cacheImg as $key => $val) {
        $img = json_decode($val, 'true');
        $imagesUrl[$img['hash']] = $img['set_uri'];
    }
    //获取缓存中不存在的商品
    if ($cacheImg) {
        $findSku = array_diff(explode(',', $hashes), array_keys($imagesUrl));
    } else {
        $findSku = explode(',', $hashes);
    }
    if ($findSku) {    //获取缓存中不存在的图片并缓存
        $arg = app('helper')->q(['hash' => $hashes]);
        $getImageUrl = app('api')->file->get('batch', $arg);
        if ($getImageUrl) {
            foreach ($getImageUrl as $key => $val) {
                $imagesUrl[$val['hash']] = $val['set_uri'];
                $img = [
                    'hash' => $val['hash'],
                    'set_uri' => $val['set_uri']
                ];
                $redis->setex($val['hash'], (env('SKU_CACHE_TIME', 86400)) * 3, json_encode($img));
            }
        }
    }
    //缓存和文件服务都没有查找到的图片指定默认图片[config.default_img]并缓存
    $diffHash = array_diff(explode(',', $hashes), array_keys($imagesUrl));
    if ($diffHash) {
        foreach ($diffHash as $key => $val) {
            $imagesUrl[$val] = config('config.default_img');
            $img = ['hash' => $val, 'set_uri' => config('config.default_img')];
            $redis->setex($val, (env('SKU_CACHE_TIME', 86400)) * 3, json_encode($img));
        }
    }
    return array_filter($imagesUrl);
}

//获取某个分类的所有子分类
function getSubs($categorys, $catId = 0, $level = 1)
{
    static $subs = array();
    foreach ($categorys as $item) {
        if ($item['pid'] == $catId) {
            $item['level'] = $level;
            $subs[] = $item;
            $subs = array_merge($subs, getSubs($categorys, $item['id'], $level + 1));
        }
    }
    return $subs;
}


/** 获取商品信息
 * @param array $goodsId
 * @return array
 */
function getGoodsInfo(array $goodsId)
{
    $redis = app('redis');
    $ids = [];
    foreach ($goodsId as $val) {
        $ids[] = config('config.goods_redisPrefix') . $val;
    }
    $cacheGoods = array_filter($redis->mget($ids));
    $goods = [];
    foreach ($cacheGoods as $key => $val) {
        $goods[] = json_decode($val, true);
    }
    //获取缓存中没有的商品信息的ID
    if ($cacheGoods) {
        $findId = array_diff($goodsId, array_column($goods, 'goods_id'));
    } else {
        $findId = $goodsId;
    }
    if ($findId) {  //从商品服务中获取商品信息

        $arg = app('helper')->q(['length' => count($goodsId), 'search' => ['goods_id' => $findId]]);
        $goodsInfo = app('api')->goods->get('goods/lists', $arg);
        if ($goodsInfo) {   //缓存商品信息
            foreach ($goodsInfo['datas'] as $key => $val) {
                $redis->setex(config('config.goods_redisPrefix') . $val['goods_id'], (env('SKU_CACHE_TIME', 86400)) * 3, json_encode($val));
            }
        }
        $goods = array_merge($goods, $goodsInfo['datas']);
    }
    return $goods;
}


/**
 * 缓存门店坐标位置
 * @return array
 */
function getSellerByDb()
{
    $field = app('db')->raw(' AsText(location) as location,id,name');
    $seller = \App\Models\Seller::select($field)->get()->toArray();
    if (!$seller) return [];
    $storeNames = [];
    $stores = array_map(function ($item) use (&$storeNames) {
        list($ln, $lo) = explode(' ', trim($item['location'], 'POINT()'));
        $storeNames[$item['id']] = json_encode(['id' => $item['id'], 'name' => $item['name']]);
        return [$ln, $lo, $item['id']];
    }, $seller);
    try {
        $redis = app('redis');
        $redis->geoadd('storesLocation', $stores);
        $redis->mset($storeNames);
    } catch (\Exception $e) {

    }
    return $seller;
}


