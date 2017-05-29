<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Student;
use App\Tools\Response as Response;
use Illuminate\Support\Facades\Validator;
use App\Tools\YunFeng;
class StuController extends Controller
{
    private $response;
    private $request;
    private $student;
    public function __construct(Response $response, Request $request, Student $student){
        $this->response = $response;
        $this->request = $request;
        $this->student = $student;
    }

    /**
     * 学员报名接口
     */
    public function stuApply(){
        if ($input = $this->request->all()) {
            $rules = [
                'name' => 'required|between:1,10',
                'phone' => 'required|regex:/^1[34578][0-9]{9}$/'
            ];
            $message = [
                'username.required' => '姓名不能为空！',
                'username.between' => '姓名不规范！',
                'phone.required' => '手机不能为空！',
                'phone.regex' => '手机不规范！',
            ];
            $validator = Validator::make($input, $rules, $message);
            $stuName = $this->request->input('name');
            $stuPhone = $this->request->input('phone');


            //表单验证
            if ($validator->passes()) {
                // 表单验证无误
                $data= array(
                    'stu_name' => $stuName,
                    'stu_phone' => $stuPhone,
                    'stu_ip' =>  $_SERVER['REMOTE_ADDR'],
                    'create_time' => date('Y-m-d H:i:s'),
                    'modify_time' => date('Y-m-d H:i:s'),
                );
                $result = $this->student->insertGetId($data);
                if (is_numeric($result) && $result>0) {

                    $yunfeng =  new YunFeng();

                    $yunfeng->applyMessage(18888940620,  $stuPhone, $stuName);

                    return $this->response->success($result);
                } else {
                    return  $this->response->error('报名失败');
                }

            } else {
                // 表单验证有误
                return  $this->response->error($validator->errors()->getMessages());
            }
        }
    }

    /**
     * 获取报名信息接口（分页）
     */
    public function getStuList()
    {

        $keyword = $this->request->input('keyword');

        if(!empty($keyword)) {
            if ($this->student->select()->where('stu_name', 'like', '%' . $keyword . '%')->count()) {
                return $this->response->success($this->student->select()->where('stu_name', 'like', '%' . $keyword . '%')->orderBy('create_time', 'desc')->paginate(10));
            } else if ($this->student->select()->where('stu_phone', 'like', '%' . $keyword . '%')->count()) {
                return $this->response->success($this->student->select()->where('stu_phone', 'like', '%' . $keyword . '%')->orderBy('create_time', 'desc')->paginate(10));
            } else {
                return $this->response->success();
            }
        }
        else
            return $this->response->success($this->student->select()->orderBy('create_time', 'desc')->paginate(10));


    }




}
