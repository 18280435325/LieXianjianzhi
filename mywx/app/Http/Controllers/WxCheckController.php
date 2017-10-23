<?php
/**
 * Created by PhpStorm.
 * User: zyy
 * Date: 2017/8/29
 * Time: 10:57
 */

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WeixinServices;
use EasyWeChat\Foundation\Application;




class WxCheckController extends Controller
{
    protected $request;
    private $service;

    public function __construct(Request $request,WeixinServices $weixinServices)
    {
        $this->request = $request;
        $this->service = $weixinServices;

    }


    public function index()
    {

        if(env('WX_IS_MEG')){  //第一次验证
            $this->urlCheck();
        }else{   //消息处理
            $this->msgTypeSeparate();
        }
    }

    /** 微信回调地址检测
     * @throws \Exception
     */
    public function urlCheck()
    {
        try{
            $weiXinInfo = $this->request->all();
            $param = [env('wx_url_token') ,$weiXinInfo['timestamp'],$weiXinInfo['nonce']];
            sort($param,SORT_STRING);
            $requestString = sha1(implode($param));
            if($requestString===$weiXinInfo['signature']){
                echo $weiXinInfo['echostr'];
            }
            return false;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }


    /** 获取accessToken
     * @throws \Exception
     */
    public function getAccessToken()
    {
        echo $this->service->getAccToken();

    }

    public function msgTypeSeparate()
    {
        $option = [
            'debug'  => true,
            'app_id' =>config('wechat.app_id'),
            'secret' => config('wechat.secret'),
            'token'  =>config('wechat.token')
        ];
        $app = new Application($option);
        $server = $app->server;
        // dd($server->getMessage());
        $server->setMessageHandler(function ($message) {
            return "您好！欢迎关注我!";
        });
        $response = $server->serve();
        return $response;
    }

}