<?php
/**
 * Created by PhpStorm.
 * User: zyy
 * Date: 2017/8/29
 * Time: 17:00
 */

namespace App\Repositories;
use App\Repositories\BaseRepository;
use App\Models\AccessToken;

class AccessTokenRepository extends BaseRepository
{
    public function model()
    {
        return AccessToken::class;
    }
    public function doCreate($data)
    {
        $tokenObj = new $this->model;
        $tokenObj->token = $data['token'];
        $tokenObj->timestring = time();
        $tokenObj->save();
    }
}