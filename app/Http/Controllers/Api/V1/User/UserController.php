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
use App\Http\Resources\HeartList;
use App\Http\Resources\UserphotoList;
use App\Models\Api\Monologue;
use App\Models\Api\UserExtend;
use App\Models\Api\UserPhoto;
use App\Models\Api\UserRequire;
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
                'salary' => 'required',

            ],[
                'sex.required' => '性别不能为空！',
                'marriage.required' => '婚姻状况不能为空！',
                'province.required' => '省份不能为空！',
                'city.required' => '城市不能为空！',
                'birthday.required' => '出生日期不能为空！',
                'tall.required' => '身高不能为空！',
                'education.required' => '学历不能为空！',
                'salary.required' => '收入不能为空！',
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

           $base64_img = $request->img;

           $re = self::_checkHead($base64_img);

           //检测头像是否合格
           if ($re['status']) {

               $file = $request->file('img_file');

               $avatarUrl = $path=$this->uploadImg($file,'head');

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
                   'photo' => UserPhotoList::collection($photo)
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

           $file = $request->file();

           $user_id = auth()->id();

           $re=$this->uploadImg($file,'photo');

           if ($re) {

              $id = DB::table('user_photo')->insertGetId([
                  'user_id' => $user_id,
                   'img' => $re[0],
                   'created_at' => date('Y-m-d H:i:s'),

               ]);

               return $this->sendJson(200,'上传成功',[
                   'id' => $id,
                   'path' => $re,
               ]);
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

           $photo_id = $request->photo_id;
           $path = $request->path;

           $re = UserPhoto::where('id',$photo_id)
               ->delete();

           if ($re) {
               if(file_exists($path)){
                   unlink($path);
               }
               return $this->sendJson(200,'删除成功',$re);
           }else{
               return $this->sendError(Code::FAIL2,'删除失败');
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
               ->first();

           if ($data) {
               return $this->sendJson(200,'获取成功',HeartList::make($data));
           }else{
               return $this->sendError(200,'暂无数据',[]);
           }

       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 新增内心独白
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function addHeart(Request $request){
       try{
           $validator = \Validator::make($request->all(), [
               'heart' => 'required',
           ],[
               'heart.required' => '内心独白内容不能为空！',
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'error' => $validator->errors()->first()
               ]);
           }

           $user = auth()->user();

           $user->heart = $request->heart;

           if ($user->save()) {
               return $this->sendJson(200,'添加成功');
           }else{
               return $this->sendError(400,'添加失败');
           }

        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 获取自己的内心独白
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function getMyHeart(){
       try{

           $user = auth()->user();

           return $this->sendJson(200,'获取成功',[
              'heart' => $user->heart?:'',
           ]);
        
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

           $user = auth()->user();

           return $this->sendJson(200,'获取成功',[
              'occupation' => $user->occupation, //职业
              'salary' => $user->salary, //薪资
              'living_place' => $user->living_place, //现居地

              'nickname' => $user->nickname, //昵称
               'tall' => $user->tall, //身高
               'marriage' => $user->marriage, //婚姻状况  1=未婚  2=离异  3=丧偶
               'native_place' => $user->native_place, //籍贯
               'school' => $user->school, //毕业院校
               'housing' => $user->housing, //住房状况
               'is_gai' => $user->is_gai, //基本资料实名后是否修改过

               'education' => $user->education, //学历
               'birthday' => $user->birthday,  //生日



           ]);

       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 修改基础资料(字段和新增时不一样)
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function editBaseInfo(Request $request){
       try{
           $param = $request->all();
           $user = auth()->user();
           if ($param) {

               if ($user->is_gai) {

                   if(array_key_exists('occupation',$param))
                   $user->occupation = $param['occupation'];
                   if (array_key_exists('salary',$param))
                   $user->salary = $param['salary'];
                   if(array_key_exists('living_place',$param))
                   $user->living_place = $param['living_place'];
                   $user->save();
                   return $this->sendJson(200,'修改成功');

               }else{
                   $user->is_gai = 1;
                   if(array_key_exists('occupation',$param))
                       $user->occupation = $param['occupation'];

                   if (array_key_exists('salary',$param))
                       $user->salary = $param['salary'];

                   if(array_key_exists('living_place',$param))
                       $user->living_place = $param['living_place'];

                   if(array_key_exists('nickname',$param))
                       $user->nickname = $param['nickname'];

                   if(array_key_exists('tall',$param))
                       $user->tall = $param['tall'];

                   if(array_key_exists('marriage',$param))
                       $user->marriage = $param['marriage'];

                   if(array_key_exists('native_place',$param))
                       $user->native_place = $param['native_place'];

                   if(array_key_exists('housing',$param))
                       $user->housing = $param['housing'];

                   if(array_key_exists('school',$param))
                       $user->school = $param['school'];
                   $user->save();

                   return $this->sendJson(200,'修改成功2');
               }

           }

        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL3, $exception->getMessage());
       }
    
    }

    /**
     * 检测是否存在相应字段
     * @param $param
     * @param $fields
     * @return bool
     */
    function  checkField($param,$fields){

        $falg = false;
        foreach ($fields as $item ){
            if (array_key_exists($item,$param)){
                $falg = true;
            }else{
                $falg = false;
                break;
            }
        }

        return $falg;
    }

    /**
     * 获取择偶要求信息
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function getAskFor(Request $request){
       try{
           $user_id = auth()->id();

           $require = UserRequire::where('user_id',$user_id)->first();

           if ($require) {

               return $this->sendJson(200,'获取成功！',$require);
           }else {

               return $this->sendJson(200,'暂无数据！',new \stdClass());
           }
        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 修改择偶要求信息
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function setAskFor(Request $request){
       try{

           $user_id = auth()->id();

           $param = $request->all();

           if ($param) {

               \DB::table('user_require')->where('user_id',$user_id)->update($param);
           }else{

               return $this->sendError(Code::FAIL2,'参数不能为空！');
           }


       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 获取家庭情况信息
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function getFamily(Request $request){
       try{

           $user_id = auth()->id();

           $family = UserExtend::where('user_id',$user_id)->first();

           if ($family) {

               return $this->sendJson(200,'获取成功！',$family);
           }else {

               return $this->sendJson(200,'暂无数据！',new \stdClass());

           }

       }catch (\Exception $exception){

          return $this->sendError(Code::FAIL, $exception->getMessage());
       }

    }

    /**
     * 修改家庭情况信息
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function editFamily(Request $request){
       try{

           $user_id = auth()->id();

           $param = $request->all();

           if ($param) {

               \DB::table('user_extend')->where('user_id',$user_id)->update($param);
           }else{

               return $this->sendError(Code::FAIL2,'参数不能为空！');
           }
           
        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 修改隐身模式
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function editHide(Request $request){
       try{

           $user = auth()->user();


           if($user->is_show == 1){
               $user->is_show = 2;
           }else{
               $user->is_show = 1;
           }

           $user->save();

           return $this->sendJson(200,'修改成功！');
        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 实名认证
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function realName(Request $request){
       try{

           $validator = \Validator::make($request->all(), [
               'img' => 'required',
               'img_file' => 'required',
               'num' => 'required',
               'name' => 'required',
           ],[
               'img.required' => '图片对象不能为空！',
               'img_file.required' => '图片文件对象不能为空！',
               'num.required' => '身份证号码不能为空！',
               'name.required' => '真实姓名不能为空！'
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'error' => $validator->errors()->first()
               ]);
           }

           $file = $request->file('img_file');

           $base64_img = $request->img;
           $num = $request->num;
           $name = $request->name;

           $re = self::_checkIdCard($base64_img,$num,$name);

           if ($re) {
               dd($re);
           }

           
        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 学历认证
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function degreeCertificate(Request $request){
       try{

           $validator = \Validator::make($request->all(), [
               'xue_img' => 'required',
           ],[
               'xue_img.required' => '图片路径参数不能为空！',
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'error' => $validator->errors()->first()
               ]);
           }

           $user = auth()->user();
           $data['xue_img'] = $request->xue_img;

           $data['user_id'] =$user->id;

           if(isset($request->xue_explain)){
               $data['xue_explain'] = $request->xue_explain;
           }

           $user_check = DB::table('user_check')->where('user_id',$data['user_id'])->first();

           if($user_check){

               DB::table('user_check')->where('user_id',$data['user_id'])->update($data);

           }else{
               DB::table('user_check')->where('user_id',$data['user_id'])->insert($data);
           }

           $user->is_xue = 1;
           $user->save();

           return $this->sendJson(200,'提交成功,请耐心等待审核！');


       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }
    

}