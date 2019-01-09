<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2019/1/6
 * Time: 20:52
 */

namespace App\Http\Controllers\Api\V1\User;


use App\Http\Components\Code;
use App\Http\Controllers\Api\BaseController;
use App\Models\Api\ShareConfig;
use Illuminate\Http\Request;

class ShareController extends BaseController
{

    /**
     * 邀请好友
     * @param Request $request
     * @return ShareController|\Illuminate\Http\JsonResponse
     */
    public function inviteFriends(Request $request){
       try{

           $user = auth()->user();

           $code = $this->getUserCode($user->id);

           $share =ShareConfig::orderBy(\DB::raw('RAND()'))
               ->take(1)
               ->first();

           if($code && $share){

               return $this->sendJson(200,'获取成功',[
                   'head' => $user->head,
                   'nickname' => $user->nickname,
                   'title' => $share->title,
                   'c_title' => $share->ad,
                   'img' => $share->img,
                   'bg_img' => $share->bg_img,
                   'code' => $code,
               ]);
           }else{

               return $this->sendError(400,'邀请失败,请稍后重试！');
           }
        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL3, $exception->getMessage());
       }
    
    }
    
}