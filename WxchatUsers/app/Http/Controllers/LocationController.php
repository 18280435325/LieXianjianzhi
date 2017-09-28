<?php
/**
 * Created by PhpStorm.
 * User: diamonds.a
 * Date: 2017/9/22
 * Time: 上午10:01
 */

namespace App\Http\Controllers;


use App\Common\Code;
use App\Common\Params;
use App\Services\LocationService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    use Params;
    private $service;

    public function __construct(Request $request, LocationService $service)
    {
        parent::__construct($request);
        $this->service = $service;
    }

    /**
     * 根据定位返回门店
     * @return mixed
     */
    public function location()
    {
        $input = $this->getParam('long', 'lat');
        if (!$input) {
            return Code::_500();
        }
        $data = $this->service->getSeller($input['long'], $input['lat']);
        return Code::Y($data);
    }
}