<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roll;
use App\Tools\Response as Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests;
use App\Tools\Qiniu;

class RollController extends Controller
{
    private $response;
    private $request;
    private $roll;
    public function __construct(Response $response, Request $request, Roll $roll){
        $this->response = $response;
        $this->request = $request;
        $this->roll = $roll;
    }


    public function getRollList() {

        return $this->response->success($this->roll->where('is_delete', 0)->select()->orderBy('modify_time', 'desc')->get());
    }

    public function addRoll() {
        if ($input = $this->request->all()) {
            $rules = [
                'img' => 'required',
            ];
            $message = [
                'img.required' => '图片URL不能为空！',
            ];
            $validator = Validator::make($input, $rules, $message);
            $img = $this->request->input('img');
            $url = $this->request->input('url');



            //表单验证
            if ($validator->passes()) {
                // 表单验证无误
                $data= array(
                    'roll_img' => $img,
                    'roll_url' => $url,
                    'create_time' => date('Y-m-d H:i:s'),
                    'modify_time' => date('Y-m-d H:i:s'),
                );
                $result = $this->roll->insertGetId($data);
                if (is_numeric($result) && $result>0) {
                    return $this->response->success($result);
                } else {
                    return  $this->response->error('轮播图添加失败');
                }

            } else {
                // 表单验证有误
                return  $this->response->error($validator->errors()->getMessages());
            }
        }

    }

    public function updateRoll() {

        if ($input = $this->request->all()) {
            $rules = [
                'newImg' => 'required',
                'rollId' => 'required|numeric'
            ];

            $message = [
                'newImg.required' => '标题不能为空！',
                'rollId.required' => 'rollId必填',
                'rollId.numeric' => 'rollId必为数字',
            ];
            $validator = Validator::make($input, $rules, $message);

            $rollId = $this->request->input('rollId');
            $newImg = $this->request->input('newImg');
            $newUrl = $this->request->input('newUrl');


            if ($validator->passes()) {

                $data= array(
                    'roll_img' => $newImg,
                    'roll_url' => $newUrl,
                    'modify_time' => date('Y-m-d H:i:s'),
                );
                $result = $this->roll->where('roll_id',$rollId)->update($data);


                if (is_numeric($result) && $result > 0) {
                    return $this->response->success($result);
                } else {
                    return $this->response->error('轮播图修改失败');
                }
            } else {
                // 表单验证有误
                return $this->response->error($validator->errors()->getMessages());
            }

        }
    }
    public function deleteRoll() {
        if ($input = $this->request->all()) {

            $rules = [
                'rollId' => 'required|numeric'
            ];
            $message = [
                'rollId.required' => 'rollId必填',
                'rollId.numeric' => 'rollId必为数字',
            ];
            $validator = Validator::make($input, $rules, $message);
            $rollId = $this->request->input('rollId');

            if ($validator->passes()) {
                $data= array(
                    'is_delete' => 1,
                    'modify_time' => date('Y-m-d H:i:s'),
                );
                $result = $this->roll->where('roll_id',$rollId)->update($data);

                if (is_numeric($result) && $result > 0) {
                    return $this->response->success($result);
                } else {
                    return $this->response->error('删除失败');
                }
            } else {
                // 表单验证有误
                return $this->response->error($validator->errors()->getMessages());
            }

        }
    }


}
