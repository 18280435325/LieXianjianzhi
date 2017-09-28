<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            $token = $request->headers->get('token');
            if(!$token){
                die('token值不能为空');
            }
            $userInfo = app('redis')->mget($token)[0];
            if(!$userInfo){
                die('token验证失败');
            }
            $openId   = json_decode(json_decode($userInfo,true)['ext'],true)["wxToken"];
            $oldToken = app('redis')->mget($openId)[0];
            if($oldToken===$token){
                return json_decode($userInfo,true);
            }else{
                die('token验证失败');
            }
        });
    }
}
