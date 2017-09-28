<?php
/**
 * Created by PhpStorm.
 * User: diamonds.a
 * Date: 2017/6/19
 * Time: 下午2:41
 */

namespace App\Repositories;


class BaseRepository
{
    private $obj = [];

    public function __get($name)
    {
        if (isset($this->obj[$name])) {
            return $this->obj[$name];
        }
        if (method_exists($this, $name)) {
            $className = $this->$name();
            if (class_exists($className)) {
                $this->obj[$name] = new $className();
                return $this->obj[$name];
            }
            return $className;
        }
        return $name;
    }
}