<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roll extends Model
{
    //设置表名
    protected $table = 'rolls';
    //设置主键
    public $primaryKey = 'roll_id';
    //不使用model默认时间戳
    public $timestamps = false;
}
