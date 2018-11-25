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
    
}