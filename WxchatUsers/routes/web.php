<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

//用户登录
$app->post('user/login','UserController@UserLogin');
//oAuth登录
$app->get('user/oauth','UserController@oAuthLogin');
//获取城市信息
$app->get('user/area','UserController@areaList');
//用户注册
$app->post('user/register','UserController@userRegister');
//发送验证码
$app->post('yzm/send','SmsController@send');
//获取门店列表
$app->get('store/list','LocationController@location');

$app->group(['middleware' => ['auth']], function () use ($app) {

    $app->group(['prefix' => 'user'], function () use ($app){
        //更改用户密码
        $app->patch('updatepwd','UserController@updatePwd');
        //退出登录
        $app->delete('outlogin','UserController@outLogin');
        //用户详细信息
        $app->get('info','UserController@getOne');
        //更改用户信息
        $app->put('update','UserController@updateInfo');
        //更改用户手机
        $app->patch('updatephone','UserController@updatePhone');
    });
    $app->group(['prefix'=>'order'],function() use ($app){
        //订单列表
        $app->get('list','OrderController@orderList');
        //订单详情
        $app->get('info','OrderController@orderInfo');
        //创建订单
        $app->post('create','OrderController@doCreate');
        //取消订单
        $app->patch('cancel','OrderController@cancelOrder');
    });
    $app->group(['prefix'=>'appraise'],function()use($app){
         //新增商品评价
        $app->post('create','GoodsAppraiseController@addAppraise');

    });
    $app->group(['prefix'=>'address'],function() use ($app){
        //新增收货地址
        $app->post('create','AddressController@doCreate');
        //修改收货地址
        $app->put('update','AddressController@doUpdate');
        //删除收货地址
        $app->delete('delete','AddressController@doDelete');
        //获取收货地址列表
        $app->get('list','AddressController@getList');
        //设置收货地址列表
        $app->put('default','AddressController@setDefault');
    });
    $app->group(['prefix'=>'goods'],function() use ($app){
        //获取门店商品分类
        $app->get('menu','GoodsController@getMenu');
        //获取分类商品
        $app->get('menugoods','GoodsController@getMenuGoods');
        //搜索商品
        $app->get('search','GoodsController@searchGoods');
        //获取商品详情
        $app->get('info','GoodsController@getSkuInfo');

    });
});




$app->get('/', function () use ($app) {
//    $redis =  app('redis');
//    $redis->del($redis->keys('SKUINFO_*'));
//     app('redis')->setex('site_name', 10, 'Lumen的redis');
//     return app('redis')->mget('site_name');
    return $app->version();
//     $list = app('db')->select("SELECT * FROM sellers_sku");
//    foreach($list as $key=>$val){
//        $sql = "UPDATE  sellers_sku SET goods_name = (SELECT goods.goods_name  FROM goods JOIN goods_sku sku ON sku.`goods_id` = goods.`id` WHERE sku.`id` =".$val->sku_id.
//            ") where id = $val->id";
//        app('db')->select($sql);
//    }
});

