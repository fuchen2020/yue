<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/11/25
 * Time: 17:20
 */

namespace App\Http\Controllers\Api\V1\User;


use App\Http\Components\Code;
use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Api\V1\Comm\UploadController;
use App\Models\Api\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends BaseController
{


    /**
     * 添加基础资料
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function baseInfo(Request $request){
        try{

            $validator = \Validator::make($request->all(), [
                'sex' => 'required',
                'marriage' => 'required',
                'province' => 'required',
                'city' => 'required',
                'birthday' => 'required',
                'tall' => 'required',
                'education' => 'required',
                'earning' => 'required',

            ],[
                'sex.required' => '性别不能为空！',
                'marriage.required' => '婚姻状况不能为空！',
                'province.required' => '省份不能为空！',
                'city.required' => '城市不能为空！',
                'birthday.required' => '出生日期不能为空！',
                'tall.required' => '身高不能为空！',
                'education.required' => '学历不能为空！',
                'earning.required' => '收入不能为空！',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'error' => $validator->errors()->first()
                ]);
            }

            $user_id = auth()->id();

            \DB::table('user')->where('id',$user_id)->update($request->all());

            return $this->sendJson(200,'资料添加成功');

        }catch (\Exception $exception){

            return $this->sendError(Code::FAIL, $exception->getMessage());
        }

    }

    /**
     * 审核用户头像
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function setHead(Request $request){
       try{

           $validator = \Validator::make($request->all(), [
               'img' => 'required',
           ],[
               'img.required' => '文件对象不能为空！'
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'error' => $validator->errors()->first()
               ]);
           }

//           $file = $request->file('img');

//           $base64_img = self::_Base64EncodeImage($file);
           $base64_img = $request->img;

           $re = self::_checkHead($base64_img);

           //检测头像是否合格
           if ($re) {

//               $avatarUrl = (new UploadController())->upload($file,'head');

               $avatarUrl ='222222';
               if ($avatarUrl) {
                   return $this->sendJson(200,'头像审核通过',['avatarUrl'=>$avatarUrl]);
               }else{
                   return $this->sendError(400,'请重新上传头像！');
               }

           }else{
               return $this->sendError(400,'头像审核不通过！');
           }

       }catch (\Exception $exception){

          return $this->sendError(Code::FAIL, $exception->getMessage());
       }

    }

    /**
     * 更新用户验证资料（头像，手机号，微信，QQ）
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function authorise(Request $request){
       try{

           $validator = \Validator::make($request->all(), [
               'mobile' => [
                   'required',
                   'regex:/^1[3456789][0-9]{9}$/'
               ],
               'code'=>'required',
           ], [
               'mobile.required' => '请输入手机号码',
               'mobile.regex' => '请输入正确的手机号码',
               'code.required' => '请输入短信验证码',
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'error' => $validator->errors()->first()
               ]);
           }

           $user_id = auth()->id();

           \DB::table('user')->insert($request->all());

           return $this->sendJson(200,'资料添加成功');

       }catch (\Exception $exception){

           return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }



}