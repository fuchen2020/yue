<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/12/20
 * Time: 21:07
 */

namespace App\Http\Controllers\Api\V1\User;


use App\Http\Components\Code;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\HeartPointList;
use App\Http\Resources\LikeMeList;
use App\Http\Resources\MyLikeList;
use App\Models\Api\UserMutual;
use App\Models\Api\UserPointLog;
use Illuminate\Http\Request;

class HeartController extends BaseController
{
    /**
     * 查询心动点纪录
     * @param Request $request
     * @param page 页数
     * @param size 条数
     * @return HeartController|\Illuminate\Http\JsonResponse
     */
    public function getHeartPointList(Request $request){
       try{

           $user_id = auth()->id();
           $page = $request->page?:1;
           $size = $request->size?:10;

           $list = UserPointLog::where('user_id',$user_id)
               ->forPage($page,$size)
               ->orderBy('created_at','desc')
               ->get();

           if ($list) {
               return $this->sendJson(200,'获取成功！',HeartPointList::collection($list));
           }else{
               return $this->sendJson(200,'暂无数据！',[]);
           }


        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }


    /**
     * 查询当前心动点数量和认证情况
     * @param Request $request
     * @return HeartController|\Illuminate\Http\JsonResponse
     */
    public function getHeartCount(Request $request){
       try{

           $user = auth()->user();

           return $this->sendJson(200,'获取成功',[
              'point_num' => $user->x_point, //心动点数
               'is_card' => $user->is_card, //实名
               'is_xue' => $user->is_xue,  //学历
               'is_gai' => $user->is_gai,  //基本资料
               'is_ze' => isset($user->require->salary)?1:0, //择偶要求
               'is_home' => isset($user->extend->parent_status)?1:0, //家庭情况
               'is_circle' => 0, //发过至少一条朋友圈
           ]);


       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 获取我喜欢的用户列表
     * @param Request $request
     * @return HeartController|\Illuminate\Http\JsonResponse
     */
    public function getMyLikeList(Request $request){
       try{

           $user_id = auth()->id();
           $page = $request->input('page')?:1;

           switch ($request->type){

               case '1': //我喜欢
                   $myLikeList = UserMutual::where('user_id',$user_id)
                       ->with('toUser');

                    break;
               case '2': //互相喜欢
                   $myLikeList = UserMutual::where('user_id',$user_id)
                       ->where('is_hu',1)
                       ->with('toUser');

                   break;
               case '3': //喜欢我
                   $myLikeList = UserMutual::where('to_user_id',$user_id)
                       ->with('user');
                   break;
           }

           $list = $myLikeList->forPage($page,10)
               ->get();



           if ($list->first()) {
                if($request->type == 3){
                    return $this->sendJson(200,'获取成功！',LikeMeList::collection($list));
                }
               return $this->sendJson(200,'获取成功！',MyLikeList::collection($list));


           }else{

               return $this->sendJson(200,'暂无数据！',[]);
           }



       }catch (\Exception $exception){

          return $this->sendError(Code::FAIL, $exception->getMessage());
       }

    }


}