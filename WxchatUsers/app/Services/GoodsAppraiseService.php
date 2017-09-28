<?php
/**
 * 商品评价服务.
 * User: zyy
 * Date: 2017/9/18
 * Time: 16:29
 */

namespace App\Services;
use App\Repositories\GoodsAppraiseRepository as appriseRep;

class GoodsAppraiseService
{
    private $appriseRep;
    public function __construct(appriseRep $appriseRepository)
    {
        $this->appriseRep = $appriseRepository;
    }

    /** 新增商品评价
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function doCreate($data)
    {
        return $this->appriseRep->create($data);
    }


}