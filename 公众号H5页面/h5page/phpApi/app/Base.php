<?php
/**
 * 基础类.
 * User: zyy
 * Date: 2017/11/26
 * Time: 11:18
 */

namespace App;
use Medoo\Medoo;

class Base{
    protected static  $mysql;
    public function __construct()
    {
        if(!self::$mysql){
            $config = require_once __DIR__."/../config/Config.php";
            $dbConfig = $config['dbConfig'];
            self::$mysql = new Medoo($dbConfig);
        }
    }
    public function returnSuccess($data){
        $data = [
            'code'=>'200',
            'status'=>'success',
            'data'=>$data
        ];
        echo json_encode($data);
        exit;
    }
    public function returnFail($error){
        $data = [
            'code'=>'500',
            'status'=>'error',
            'errInfo'=>$error
        ];
        echo json_encode($data);
        exit;
    }



}





