<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;
use App\Tools\Response as Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests;
use App\Tools\Qiniu;
define('MAX_SIGNATURE_APPLY_SIZE', 1048576);//1MB
class NewsController extends Controller
{
    private $response;
    private $request;
    private $news;
    private $qiniu;
    public function __construct(Response $response, Request $request, News $news, Qiniu $qiniu){
        $this->response = $response;
        $this->request = $request;
        $this->news = $news;
        $this->qiniu =$qiniu;
    }


    public function getNewsList() {

        if ($input = $this->request->all()) {
            return $this->response->success($this->news->where('is_delete', 0)->select()->orderBy('is_top', 'desc')->orderBy('modify_time', 'desc')->paginate(10));
        }
    }

    public function addNews() {
        if ($input = $this->request->all()) {
            $rules = [
                'title' => 'required',
                'content' => 'required',
                 'abstract' => 'required'
            ];
            $message = [
                'title.required' => '标题不能为空！',
                'content.required' => '内容不能为空！',
                'abstract.required' => '简介不能为空！',
            ];
            $validator = Validator::make($input, $rules, $message);
            $title = $this->request->input('title');
            $content = $this->request->input('content');
            $imgUrl =  $this->request->input('imgUrl');
            $abstract = $this->request->input('abstract');

            //表单验证
            if ($validator->passes()) {
                // 表单验证无误
                $data= array(
                    'news_title' => $title,
                    'news_content' => $content,
                    'news_abstract' => $abstract,
                    'img_url' => $imgUrl,
                    'create_time' => date('Y-m-d H:i:s'),
                    'modify_time' => date('Y-m-d H:i:s'),
                );
                $result = $this->news->insertGetId($data);
                if (is_numeric($result) && $result>0) {
                    return $this->response->success($result);
                } else {
                    return  $this->response->error('新闻添加');
                }

            } else {
                // 表单验证有误
                return  $this->response->error($validator->errors()->getMessages());
            }
        }

    }

    public function updateNews() {

        if ($input = $this->request->all()) {
            $rules = [
                'newTitle' => 'required',
                'newContent' => 'required',
                'newAbstract' => 'required',
                'newsId' => 'required|numeric'
            ];

            $message = [
                'newTitle.required' => '标题不能为空！',
                'newContent.required' => '内容不能为空！',
                'newAbstract.required' => '内容不能为空！',
                'newsId.required' => 'newsId必填',
                'newsId.numeric' => 'newsId必为数字',
            ];
            $validator = Validator::make($input, $rules, $message);

            $newsId = $this->request->input('newsId');
            $newTitle = $this->request->input('newTitle');
            $newContent = $this->request->input('newContent');
            $newImgUrl = $this->request->input('newImgUrl');
            $newAbstract = $this->request->input('newAbstract');

            if ($validator->passes()) {

                $data= array(
                    'news_title' => $newTitle,
                    'news_content' => $newContent,
                    'news_abstract' => $newAbstract,
                    'img_url' => $newImgUrl,
                    'modify_time' => date('Y-m-d H:i:s'),
                );
                $result = $this->news->where('news_id',$newsId)->update($data);


                if (is_numeric($result) && $result > 0) {
                    return $this->response->success($result);
                } else {
                    return $this->response->error('新闻修改失败');
                }
            } else {
                // 表单验证有误
                return $this->response->error($validator->errors()->getMessages());
            }

        }
    }
    public function deleteNews() {
        if ($input = $this->request->all()) {

            $rules = [
                'newsId' => 'required|numeric'
            ];
            $message = [
                'newsId.required' => 'newsId必填',
                'newsId.numeric' => 'newsId必为数字',
            ];
            $validator = Validator::make($input, $rules, $message);
            $newsId = $this->request->input('newsId');

            if ($validator->passes()) {
                $data= array(
                    'is_delete' => 1,
                    'modify_time' => date('Y-m-d H:i:s'),
                );
                $result = $this->news->where('news_id',$newsId)->update($data);

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

    public function topNews() {
        if ($input = $this->request->all()) {

            $rules = [
                'newsId' => 'required|numeric'
            ];
            $message = [
                'newsId.required' => 'newsId必填',
                'newsId.numeric' => 'newsId必为数字',
            ];
            $validator = Validator::make($input, $rules, $message);
            $newsId = $this->request->input('newsId');

            if ($validator->passes()) {

                $top2 = $this->news->where('is_top',2)->select()->count();

                if($top2 == 0) {
                    $data= array(
                        'is_top' => 2,
                    );
                    $result = $this->news->where('news_id',$newsId)->update($data);

                }
                else {
                    $this->news->where('is_top',1)->update(['is_top'=>0]);
                    $this->news->where('is_top',2)->update(['is_top'=>1]);
                    $data= array(
                        'is_top' => 2,
                    );
                    $result = $this->news->where('news_id',$newsId)->update($data);
                }

                if (is_numeric($result) && $result > 0) {

                    return $this->response->success($result);
                } else {
                    return $this->response->error('置顶失败');
                }
            } else {
                // 表单验证有误
                return $this->response->error($validator->errors()->getMessages());
            }

        }
    }

    public function uploadImg(){

        if(!$this->request->hasFile('file'))
            return $this->response->error('无文件');//无文件
        if(!$this->request->file('file')->isValid())
            return $this->response->error('文件上传出错');//文件传输出错

        $clientName = $this->request->file('file')->getClientOriginalName();
        $extension =  $this->request->file('file')->getClientOriginalExtension();
        $newName = md5(date('ymdhis').$clientName).".".$extension;

        $fileSize = $this->request->file('file')->getClientSize();

        if($fileSize > MAX_SIGNATURE_APPLY_SIZE)
            return $this->response->error('文件超出大小限制')  ;//文件超出大小

        $res = $this->qiniu->upload($newName, $this->request->file('file'),'muyu-hhxc');

        $fileType = $this->request->file('file')->getMimeType();// image/jpeg

        if(!starts_with($fileType, 'image/')){
            //文件类型不符合规范
            return $this->response->error('文件类型不符');
        }

        if($res == 'success'){
            $link = 'http://opme49mo1.bkt.clouddn.com/'.$newName;
            return $this->response->success(array('link'=>$link));
        }else{
            return $this->response->error('图片上传出错');
        }

    }

    public function QiniuToken() {
        $result = $this->qiniu->getToken('muyu-hhxc');
        return json_encode(array("uptoken" => $result));
    }

}
