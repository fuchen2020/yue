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
use App\Http\Resources\UserphotoList;
use App\Models\Api\Monologue;
use App\Models\Api\User;
use App\Models\Api\UserPhoto;
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
               'img_file' => 'required',
           ],[
               'img.required' => '文件对象不能为空！',
               'img_file.required' => '文件对象不能为空！'
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'error' => $validator->errors()->first()
               ]);
           }

           $file = $request->file('img_file');

           $base64_img = $request->img;

           $re = self::_checkHead($base64_img);

           //检测头像是否合格
           if ($re['status']) {

               $avatarUrl = (new UploadController())->upload($file,'head');

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
               'phone' => [
                   'required',
                   'regex:/^1[3456789][0-9]{9}$/'
               ],
               'head'=>'required',
               'qq'=>'required',
               'wx'=>'required',
               'code'=>'required',
               'key'=>'required',

           ], [
               'head.required' => '请上传真实头像',
               'qq.required' => '请输入真实QQ号码',
               'wx.required' => '请输入真实微信号码',
               'phone.required' => '请输入真实手机号码',
               'phone.regex' => '请输入正确的手机号码',
               'code.required' => '请输入短信验证码',
               'key.required' => '验证码KEY不能为空',
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'error' => $validator->errors()->first()
               ]);
           }

           $verifyData = \Cache::get($request->key);

           if (!$verifyData) {

               return response()->json([
                   'code' => 400,
                   'status' => false,
                   'msg' => '验证码已失效'
               ]);
           }

           if (!hash_equals($verifyData['code'], $request->code)) {
               return response()->json([
                   'code' => 400,
                   'status' => false,
                   'msg' => '验证码错误'
               ]);

           }


           $user_id = auth()->id();

           $data=[

             'phone' => $request->phone,
             'head'  => $request->head,
             'qq'  => $request->qq,
             'wx'  => $request->wx,
           ];

           \DB::table('user')->where('id',$user_id)->update($data);

           return $this->sendJson(200,'资料添加成功');

       }catch (\Exception $exception){

           return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 获取相册列表
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function getPhotoList(Request $request){
       try{

           $user_id = auth()->id();
           $photo = UserPhoto::where('user_id',$user_id)->get();

           if ($photo) {
               return $this->sendJson(200,'获取相册成功',[
                   'photo' => UserphotoList::collection($photo)
               ]);
           }else{

               return $this->sendError(200,'暂无数据');
           }

        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 新增相片
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function addPhoto( Request $request){
       try{
           $validator = \Validator::make($request->all(), [
               'img' => 'required',
           ],[
               'img.required' => '文件不能为空！',
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'error' => $validator->errors()->first()
               ]);
           }

           $file = $request->file('img');

           $user_id = auth()->id();

            $re = (new UploadController())->upload($file,'photo');

           if ($re) {

               DB::table('user_photo')->insert([
                  'user_id' => $user_id,
                   'img' => $re,
                   'created_at' => date('Y-m-d H:i:s'),

               ]);

               return $this->sendJson(200,'上传成功',$re);
           }else{
               return $this->sendError(Code::FAIL2,'上传失败');
           }
           
        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 删除相片
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function delPhoto( Request $request){
       try{

           $validator = \Validator::make($request->all(), [
               'photo_id' => 'required',
               'path' => 'required',
           ],[
               'photo_id.required' => '文件ID不能为空！',
               'path.required' => '文件路径不能为空！'
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'error' => $validator->errors()->first()
               ]);
           }

           $photo_id = $request->id;
           $path = $request->path;
           $user_id = auth()->id();

           $re = UserPhoto::where('id',$photo_id)
               ->where('user_id',$user_id)
               ->delete();

           if ($re) {
               if(file_exists($path)){
                   unlink($path);
               }
               return $this->sendJson(200,'上传成功',$re);
           }else{
               return $this->sendError(Code::FAIL2,'上传失败');
           }


       }catch (\Exception $exception){

          return $this->sendError(Code::FAIL, $exception->getMessage());
       }

    }

    /**
     * 获取一条随机内心独白
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function getHeart(){
       try{

           $data=Monologue::orderBy(\DB::raw('RAND()'))
               ->take(1)
               ->get();
           if ($data) {

               return $this->sendJson(200,'获取成功');
           }else{
               return $this->sendError(200,'暂无数据');
           }



       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 新增内心独白
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function addHeart(){
       try{
        
           
        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 获取自己的内心独白--------------------------
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function getMyHeart(){
       try{
        
           
        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 获取基础资料(就是前面新增基础资料的信息)
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function getBaseInfo(){
       try{
           

        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 修改基础资料
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function editBaseInfo(){
       try{
        
           
        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }
}