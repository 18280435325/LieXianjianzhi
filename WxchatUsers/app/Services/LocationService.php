<?php
/**
 * Created by PhpStorm.
 * User: diamonds.a
 * Date: 2017/9/22
 * Time: 上午10:21
 */

namespace App\Services;


use App\Models\Seller;

class LocationService
{
    /**
     * 获取最近位置的门店
     * @param $long
     * @param $lat
     * @return array
     */
    public function getSeller($long, $lat)
    {
        $redis = app('redis');
        $bstop = $redis->exists('storesLocation');
        if (!$bstop) {
            getSellerByDb();
        }
        $seller = $this->sellerLocation($long, $lat);
        return $seller;
    }

    /**
     * 获取门店信息
     * @param $long
     * @param $lat
     * @return array
     */
    private function sellerLocation($long, $lat)
    {
        $redis = app('redis');
        $distance = config('config.delivery_distance');
        $ids = $redis->georadius('storesLocation', $long, $lat, $distance, 'm', 'ASC');
        if (!$ids) {
            return [];
        }
        $sellers = $redis->mget($ids);
        if (!$sellers) {
            $sellers = Seller::select('id', 'name')->whereIn('id', $ids)->get()->toArray();
        } else {
            $sellers = array_map(function ($item) {
                return json_decode($item, true);
            }, $sellers);
        }
        return $sellers;
    }


}