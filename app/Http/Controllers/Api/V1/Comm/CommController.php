<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/11/25
 * Time: 15:45
 */

namespace App\Http\Controllers\Api\V1\Comm;


use App\Http\Components\Code;
use App\Http\Controllers\Api\BaseController;
use App\Models\Api\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommController extends BaseController
{

    /**
     * 获取开屏背景图
     * @param Request $request
     * @return CommController|\Illuminate\Http\JsonResponse
     */
    public function getBackImage(Request $request){
       try{
        
           $backImg = Banner::where('type',1)->first();

           if ($backImg) {
                return response()->json([
                   'code'=>200,
                    'msg'=>'获取成功！',
                    'status'=> true,
                    'data' =>[
                        'img'=>$backImg->img,
                    ]

                ]);
           }else{
               return $this->sendError(Code::FAIL2, '暂无数据');
           }
        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 添加模板ID
     * @param Request $request
     * @return CommController|\Illuminate\Http\JsonResponse
     */
    public function addFormID(Request $request){
       try{

           $validator = \Validator::make($request->all(), [
               'code' => 'required',
           ]);

           if ($validator->fails()) {
               return $this->sendError(Code::FAIL2,$validator->errors()->first());
           }

           $user_id = auth()->id();

           $re = \DB::table('user_form_id')->insert([

                'user_id' => $user_id,
                'form_id' => $request->form_id,
                'end_time' => time()+ 259200, //当前时间戳加3天
                'created_at' => date('Y-m-d H:i:s'),
            ]);

           if ($re) {

               return $this->sendJson(200,'添加ID成功');

           }else{
               return $this->sendError(Code::FAIL3,'添加失败');
           }

       }catch (\Exception $exception){

          return $this->sendError(Code::FAIL, $exception->getMessage());
       }

    }


    /**
     * 发送短信(注册,改密)
     * @param SendSmsRequest $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function sendSms(SendSmsRequest $request){

        try{

            $phone = $request->input('phone');
            $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);
            $easySms = new EasySms(config('easysms.config'));
            try {
                switch ($request->input('type')){
                    //注册
                    case 'reg':
                        $result = $easySms->send($phone, [
                            'template' => config('easysms.tempLet_id.reg'),
                            'data' => [
                                'code' => $code
                            ],
                        ]);
                        break;
                    //修改密码
                    case 'revise':
                        $result = $easySms->send($phone, [
                            'template' => config('easysms.tempLet_id.revise'),
                            'data' => [
                                'code' => $code
                            ],
                        ]);
                        break;
                }

            } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
                $message = $exception->getException('aliyun')->getMessage();
                return response()->json([
                    'code'=>400,
                    'msg'=>$message ?: '短信发送异常',
                    'status'=>false,
                ]);
            }

            $key = 'verificationCode_'.str_random(15);
            $expiredAt = now()->addMinutes(10);
            // 缓存验证码 10分钟过期。
            \Cache::put($key, ['phone' => $phone, 'code' => $code], $expiredAt);
            return response()->json([
                'code'=>200,
                'msg'=>'短信发送成功!',
                'status'=>true,
                'data'=>[
                    'verification_key'=>$key
                ],
            ]);

        }catch (\Exception $exception){

            return response()->json(['msg' => '方法执行异常','code'=>500,'err'=>$exception->getMessage()]);
        }

    }

}