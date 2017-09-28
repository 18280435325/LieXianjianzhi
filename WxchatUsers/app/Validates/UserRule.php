<?php
/**
 * Created by PhpStorm.
 * User: diamonds.a
 * Date: 2017/7/4
 * Time: 下午2:33
 */

namespace App\Validates;


trait UserRule
{
    protected $userRule = [
        'name' => 'required|string',
        'pwd' => 'required|string',
        'phone' => 'string',
        'city_id' => 'integer',
        'area_id' => 'integer',
        'address' => 'string',
        'ext' => 'array',
    ];

    protected $extendRule = [
        'ext.wxToken'=>'string'
    ];

    //获取验证规则
    protected function getRules()
    {
        $rules = $this->userRule;
        if (isset($rules['ext'])) {
            $rules = array_merge($rules, $this->extendRule);
        }

        return $rules;
    }

    //添加规则
    public function doCreateRule($data = [])
    {
        $validator = app('validator')->make($data, $this->getRules());
        if ($validator->fails()) {
            throw  new \Exception(join(' | ', $validator->errors()->all()));
        }
    }
}