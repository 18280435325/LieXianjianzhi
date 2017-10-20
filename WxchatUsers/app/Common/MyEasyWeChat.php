<?php
/**
 * Created by PhpStorm.
 * User: zyy
 * Date: 2017/9/28
 * Time: 13:46
 */
namespace App\Common;
use EasyWeChat\Foundation\Application;
use Doctrine\Common\Cache\RedisCache;

trait MyEasyWeChat {
    public function getEasyWeChatObj($option=[])
    {
        $cacheDriver = new RedisCache();
// 创建 redis 实例,使用redis缓存
        $redis = new \Redis();
        $redis->connect(env('REDIS_HOST','127.0.0.1'), env('REDIS_PORT','6379'));
        $cacheDriver->setRedis($redis);
        if(!$option){
            $option = [
                'debug'  => false,
                'app_id' => config('wechat.app_id'),
                'secret' => config('wechat.secret'),
                'token'  => config('wechat.token'),
                'aes_key' => config('wechat.aes_key'), // 可选
                'cache'   => $cacheDriver,
            ];
        }
        $app = new Application($option);
//        $accessToken = $app->access_token; // EasyWeChat\Core\AccessToken 实例
//        $token = $accessToken->getToken();
//        dd($token);
        return $app;
    }

}