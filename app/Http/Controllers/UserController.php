<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tools\Response as Response;
use App\Http\Requests;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $response;
    private $request;
    public function __construct(Response $response, Request $request){
        $this->response = $response;
        $this->request = $request;
    }

    //
    /**
     * 用户登录接口
     * @return string
     */
    public function login()
    {
        if ($input = $this->request->all()) {
            $rules = [
                'username' => 'required|between:1,10',
                'password' => 'required|regex:/^(?![^\d]+$)(?![^a-zA-Z]+$)[\da-zA-Z!.#$%^&*=_~@]{6,16}$/'
            ];
            $message = [
                'username.required' => '用户名不能为空！',
                'username.between' => '用户名不规范！',
                'password.required' => '密码不能为空！',
                'password.regex' => '密码格式不对',
            ];
            $validator = Validator::make($input, $rules, $message);
            $username = $this->request->input('username');
            $password = $this->request->input('password');

            //表单验证
            if ($validator->passes()) {
                // 表单验证无误
                $userModel = new User();
                $where = array(
                    'username' => $username,
                    'password' => $this->encryptPassword($password, $username),
                    'is_delete' => 0
                );
                $result = $userModel->where($where)->count();
                if ($result == 1) {
                    return $this->response->success($this->updateToken($username,1));
                } else {
                    return  $this->response->error('用户么／密码错误');
                }

            } else {
                // 表单验证有误
                return  $this->response->error($validator->errors()->getMessages());
            }
        }
    }


    /**
     * 更新token
     * @param $username
     * @param $isRebuild
     * @return string
     */
    private function updateToken($username, $isRebuild)
    {
        $where = array('username' => $username);
        switch ($isRebuild) {
            case 0:
                //不重新生成token
                $tokenActiveTime = date('Y-m-d H:i:s', time() + 7200);
                $data = array('token_active_time' => $tokenActiveTime);
                break;
            case 1:
                //重新生成token
                $token = str_random(40);
                $tokenActiveTime = date('Y-m-d H:i:s', time() + 7200);
                $data = array('token' => $token, 'token_active_time' => $tokenActiveTime);
                break;
        }

        $userModel = new User();
        $res = $userModel->where($where)->update($data);
        if ($res) {
            return $token;
        } else {
            return $res;
        }
    }


    /**
     * 密码加密哈希加密
     * @param $password 密码
     * @param $phone 手机号
     * @return mixed
     */
    private function encryptPassword($password, $phone)
    {
        $hash = hash_pbkdf2("sha256", $password, $phone, 1000, 20);
        return $hash;
    }
}
