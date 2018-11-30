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
                'avatarUrl' => 'required',
                'nickName' => 'required',
                'gender' => 'required',
                'province' => 'required',
                'city' => 'required',
            ],[
                'avatarUrl.required' => '头像不能为空！',
                'nickName.required' => '昵称不能为空！',
                'gender.required' => '性别不能为空！',
                'province.required' => '省份不能为空！',
                'city.required' => '城市不能为空！',
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

           $file = $request->file('img');

           $base64_img = self::_Base64EncodeImage($file);

           $re = self::_checkHead($base64_img);

           //检测头像是否合格
           if ($re) {

               $avatarUrl = (new UploadController())->upload($file,'head');

//               $avatarUrl ='222222';
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





}