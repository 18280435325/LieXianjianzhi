<?php
/**
 * Created by PhpStorm.
 * User: zyy
 * Date: 2017/8/29
 * Time: 16:35
 */


namespace App\Services;
use GuzzleHttp\Client;
use App\Models\AccessToken;
use App\Repositories\AccessTokenRepository as accRep;

class WeixinServices
{
    private  $accRep;

    public function __construct(accRep $accRep)
    {
     $this->accRep = $accRep;
    }

    public function requestToken()
    {
//        $client = new Client([
//            // Base URI is used with relative requests
//            'http_errors' => false,
//            'timeout'     => 15,
//            // You can set any number of default request options.
//        ]);
        $appid  = env('WX_APPID');
        $secret = env('WX_APPSECRET');
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&"
            ."secret=$secret";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $jsoninfo = json_decode($output, true);
        $access_token = $jsoninfo["access_token"];
//        $response = $client->get($url)->getBody()->getContents();
        return $access_token;
    }

    public function doCreate($token)
    {
        $this->accRep->doCreate(['token'=>$token]);
    }

    public function getAccToken()
    {
        try{
            $lastToken = AccessToken::orderBy('id','desc')->first();

            if(!$lastToken){
                $token = $this->requestToken();
                if($token){    //添加token记录
                    $this->doCreate($token);
                }
            }else{
                if(time() - ($lastToken->timestring) <7200){   //token2个小时过期
                    return $lastToken->token;
                }else{
                    $token = $this->requestToken();
                    if($token){    //添加token记录
                        $this->doCreate($token);
                    }
                }
            }
            return $token;
        }catch (\Exception $e){
            dd($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

}
