<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Common\Code;
use App\Common\Params;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use Params;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request=$request;
    }
    public function __get($name)
    {
        if($name=='user'){
            $userInfo = $this->request->user();
            return $userInfo;
        }
    }

}
