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
use App\Models\Api\User;
use App\Models\Api\UserAuth;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class LoginController extends BaseController
{
    /**
     * 用户登录
     * @param Request $request
     * @return LoginController|\Illuminate\Http\JsonResponse
     */
   public function login(Request $request){

       try{

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

                   $info = [
                       'token' => auth()->tokenById($userAuth->id),
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


}