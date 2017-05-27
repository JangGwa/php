<?php

/**
 * Created by PhpStorm.
 * User: eda
 * Date: 2017/5/5
 * Time: 上午10:31
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    //设置表名
    protected $table = 'users';
    //设置主键
    public $primaryKey = 'user_id';
    //不使用model默认时间戳
    public $timestamps = false;

}