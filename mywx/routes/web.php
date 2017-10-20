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
$app->group(['prefix'=>'wx'],function() use ($app){
    $app->get('index','WxCheckController@index');
    $app->get('gettoken','WxCheckController@getAccessToken');
});

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->get('wx/test', function () use ($app) {
    return $app->version();
});


