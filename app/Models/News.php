<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
     //设置表名
    protected $table = 'news';
    //设置主键
    public $primaryKey = 'news_id';
    //不使用model默认时间戳
    public $timestamps = false;
}
