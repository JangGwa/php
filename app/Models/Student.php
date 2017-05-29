<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    //
    //设置表名
    protected $table = 'students';
    //设置主键
    public $primaryKey = 'stu_id';
    //不使用model默认时间戳
    public $timestamps = false;
}
