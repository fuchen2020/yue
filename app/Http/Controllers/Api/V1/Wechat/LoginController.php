<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/11/21
 * Time: 22:42
 */

namespace App\Http\Controllers\Api\V1\Wechat;


use App\Http\Components\Code;
use App\Http\Controllers\Api\BaseController;
use App\Models\Api\UserAuth;
use App\Models\Api\UserVip;
use EasyWeChat\Factory;
use Illuminate\Http\Request;

class LoginController extends BaseController
{
    /**
     * 用户登录
     * @param Request $request
     * @return LoginController|\Illuminate\Http\JsonResponse
     */
   public function login(Request $request){

       try{

           if($request->id){

               return [
                   'code' =>200,
                   'data' =>[
                       'token' =>  auth()->tokenById($request->id),
                   ]
               ];
           }



           $validator = \Validator::make($request->all(), [
               'code' => 'required',
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'error' => $validator->errors()->first()
               ]);
           }

           $config=config('wechat.config');

           $app = Factory::miniProgram($config);
           $user = $app->auth->session($request->input('code'));
           $openid = $user['openid'];
           $os_info = $request->input('os_info');
           $lon = $request->input('lon');
           $lat = $request->input('lat');

           if ($openid) {
               $userAuth = (new UserAuth())->where('m_openid', $openid)->first();
               //判断当前用户在数据库是否存在
               if ($userAuth) {
                   $user_id = $userAuth->id;
                   if (UserVip::where('user_id',$user_id)->where('end_time','>',date('Y-m-d H:i:s'))->exists()){//判断当前是否充值
                       $end_time=UserVIP::where('user_id',$user_id)->first();
                       $is_vip = $end_time['end_time'];
                   }else{
                       $is_vip = false;
                   }

                   $info = [
                       'token' => auth()->tokenById($userAuth->id),
                       'is_vip' => $is_vip?true:false,
                       'is_base_info' => $userAuth->head?true:false,
                       'is_real_name' => $userAuth->is_card?true:false,
                       'is_fen' =>  $userAuth->is_fen?true:false,
                       'is_tui' =>  $userAuth->is_tui?true:false,
                   ];
                   return response()->json([
                       'code' =>200,
                       'status' => true,
                       'msg' => '登陆成功!',
                       'data' =>$info
                   ]);
               }else{
                  //新用户注册
                   \DB::beginTransaction();
                   try{
                       $user_id = \DB::table('user_auth')->insertGetId([
                          'm_openid' => $openid,
                           'lgt' => $lat,
                           'lon' => $lon,
                           'os_info' => $os_info,
                           'created_at' => date('Y-m-d H:i:s'),
                       ]);

                       \DB::table('user')->insert([
                           'id' => $user_id,
                           'created_at' => date('Y-m-d H:i:s'),
                       ]);

                       $info = [
                           'token' => auth()->tokenById($userAuth->id),
                           'is_vip' => false,
                           'is_base_info' => false,
                           'is_real_name' => false,
                           'is_fen' => false,
                           'is_tui' => false,
                       ];
                       \DB::commit();
                       return response()->json([
                           'code' =>200,
                           'status' => true,
                           'msg' => '登陆成功!',
                           'data' =>$info
                       ]);

                   }catch (\Exception $exception){
                       \DB::rollBack();
                       return response()->json([
                           'code' =>400,
                           'status' => false,
                           'msg' => '登陆授权失败!',
                           'data' =>''
                       ]);
                   }

               }
           }

        
       }catch (\Exception $exception){

           return $this->sendError(Code::FAIL, $exception->getMessage());
       }

   }

    /**
     * 解密微信手机号
     * @param Request $request
     * @return LoginController|\Illuminate\Http\JsonResponse
     */
   public function decryptPhone(Request $request){
      try{

          $validator = \Validator::make($request->all(), [
              'session' => 'required',
              'iv' => 'required',
              'encryptData' => 'required',
          ],[
              'session.required'=>'解密session数据不能为空',
              'iv.required'=>'解密iv数据不能为空',
              'encryptData.required'=>'解密encryptData数据不能为空',
          ]);

          if ($validator->fails()) {
              return response()->json([
                  'code' => 400,
                  'error' => $validator->errors()->first()
              ]);
          }

          $config=config('wechat.config');

          $app = Factory::miniProgram($config);

          $decryptedData = $app->encryptor->decryptData($request->input('session'), $request->input('iv'), $request->input('encryptData'));

          if ($decryptedData) {

              dd($decryptedData);
          }

       
      }catch (\Exception $exception){
   
         return $this->sendError(Code::FAIL, $exception->getMessage());
      }
   
   }

}