<?php
/**
 * 收货地址服务.
 * User: zyy
 * Date: 2017/9/19
 * Time: 13:34
 */

namespace App\Services;
use App\Repositories\AddressRepository;
use App\Models\DeliveryAddress;

class AddressService
{
    private $repository;
    public function __construct(AddressRepository $addressRepository)
    {
        $this->repository = $addressRepository;
    }

    /** 新增收货地址
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function addAddress($data)
    {
        $db = app('db');
        $db->beginTransaction();
        try{
            $countAddress = DeliveryAddress::where('uid',$data['uid'])->count('id');
            if($countAddress>DeliveryAddress::MAX_COUNT-1){   //最多收货地址个数检测
                throw new \Exception(config('code._616'));
            }
            $addId = $this->repository->create($data);
            if($data['default']==='1'){     //是否设为默认地址
                $this->repository->setDefault($data['uid'],$addId);
            }
            $db->commit();
        }catch (\Exception $e){
            $db->rollBack();
            throw new \Exception($e->getMessage());
        }
        return $addId;
    }

    /** 更改收货地址信息
     * @param $data
     * @throws \Exception
     */
    public function doUpdate($data)
    {
        $temp['id'] = $data['id'];
        foreach($data as $key=>$val){
            if(!empty($val)){
                $temp[$key] = $val;
            }
        }
        $this->repository->update($temp);
//        $db=app('db');
//        $db->enableQueryLog();
//        $log = $db->getQueryLog();
//        dd($log);
    }

    public function doDelete($uid,$id){
        $this->repository->remove($uid,$id);
    }

    public function getList($uid)
    {
       return  $this->repository->getList($uid);
    }
    public function doDefault($uid,$address_id)
    {
        $this->repository->setDefault($uid,$address_id);
    }
}
