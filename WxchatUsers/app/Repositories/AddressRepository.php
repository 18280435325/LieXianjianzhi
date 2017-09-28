<?php
/**
 * 用户收货地址数据源.
 * User: zyy
 * Date: 2017/9/19
 * Time: 10:39
 */

namespace App\Repositories;
use App\Repositories\BaseRepository;
use App\Models\DeliveryAddress;

class AddressRepository extends BaseRepository
{
    public  $filed = [
        'id'=>'id',
        'uid'=>'uid',
        'address'=>'address',
        'delivery_user'=>'delivery_user',
        'phone'=>'phone',
        'city_id'=>'city_id',
        'area_id'=>'area_id',
        'default'=>'default'
    ];
    public function field($table='')
    {
        $selectFiled = [];
        if($table){
            foreach($this->filed as $key=>$val){
                $selectFiled[] = $table.'.'.$key.' as '.$val;
            }
        }else{
            foreach($this->filed as $key=>$val){
                $selectFiled[] = $key." as ".$val;
            }
        }
        return $selectFiled;
    }
    public function model()
    {
        return DeliveryAddress::class;
    }

    /** 新增收货地址
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function create($data)
    {
        $addObj = DeliveryAddress::create($data);
        if(!$addObj){
            throw new \Exception(config('code._612'));
        }
        return $addObj->id;
    }

    /** 修改收货地址
     * @param $data
     * @throws \Exception
     */
    public function update($data)
    {
        $address = DeliveryAddress::find($data['id']);
        foreach($data as $key=>$val){
            if(in_array($key,array_keys($this->filed))){
                $address->$key = $val;
            }
        }
        $bool = $address->save();
        if(!$bool){
            throw new \Exception(config('code._613'));
        }
    }

    /**  设置默认收货地址
     * @param $uid  [用户ID]
     * @param $address_id [收货地址ID]
     * @throws \Exception
     */
    public function setDefault($uid,$address_id)
    {
        $db = app('db');
        $db->beginTransaction();
        try{
            DeliveryAddress::where('uid',$uid)->update(['default'=>'0']);
            DeliveryAddress::where('id',$address_id)->update(['default'=>'1']);
            $db->commit();
        }catch (\Exception $e){
            $db->rollBack();
            throw new \Exception(config('code._614'));
        }
    }

    /** 删除用户收货地址
     * @param $uid [用户ID]
     * @param $address_id [收货地址ID]
     * @throws \Exception
     */
    public function remove($uid,$address_id)
    {
        $address = DeliveryAddress::find($address_id);
        if($address->uid!=$uid){
            throw new \Exception(config('code._615'));
        }
        if(!$address->delete()){
            throw new \Exception(config('code._615'));
        }
    }

    /**  获取用户的收货地址列表
     * @param $uid
     * @return array
     */
    public function getList($uid)
    {
        return DeliveryAddress::where('uid',$uid)->select($this->field())->get()->toArray();
    }


}