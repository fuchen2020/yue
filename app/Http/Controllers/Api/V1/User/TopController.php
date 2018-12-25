<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/12/23
 * Time: 21:04
 */

namespace App\Http\Controllers\Api\V1\User;


use App\Http\Components\Code;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\TopList;
use App\Models\Api\User;
use Illuminate\Http\Request;

class TopController extends BaseController
{

    /**
     * 获取推荐列表
     * @param Request $request
     * @param sex
     * @param  age1 age2
     * @param city
     * @return TopController|\Illuminate\Http\JsonResponse
     */
    public function getList(Request $request){
       try{

//           $page = $request->page ?: 1;
//           $size=$request->size ?: 10;

           $yk = auth()->user();
           $user=new User();
           //不是隐身模式
           $user=$user->where('is_show',1);
           //择偶条件
           //性别
           if ($request->input('sex')!=''){
               switch ($request->input('sex')) {
                   case '1':
                       $user = $user->where('sex', 1);
                       break;
                   case '2':
                       $user = $user->where('sex', 2);
                       break;
                   case '3':
                       $user = $user;
                       break;
               }
           }else{
               //男推女
               if($yk->sex == 1){
                   $user = $user->where('sex',2);
               }else{
                   $user = $user->where('sex',1);
               }
           }

           //年龄
           if ($request->input('age1')!='' && $request->input('age2')!=''){

                $age1 = date('Y')-$request->age1?:18;
                $age2 = date('Y')-$request->age2?:28;

               $user = $user->whereBetween('birthday',[$age1.'01',$age2.'12']);
           }

           //城市
           if ($request->input('city')!=''){

               $user = $user->where('city',$request->city);
           }

            $list = $user->with('photo')
                    ->with(['vip'=>function ($query){
                        $query->where('end_time','>',date('Y-m-d H:i:s'));
                    }])
                    ->orderBy(\DB::raw('RAND()'))
                    ->take(10)
                    ->get();

            // dd($list->toArray());

           if ($list) {

               return $this->sendJson(200,'获取成功！',TopList::collection($list));

           }else{

               return $this->sendJson(200,'暂无数据！',new \stdClass());
           }


       }catch (\Exception $exception){

           dump($exception);
          return $this->sendError(Code::FAIL3, $exception->getMessage());
       }

    }

    /**
     * 获取用户资料详情
     * @param Request $request
     * @return TopController|\Illuminate\Http\JsonResponse
     */
    public function getDetail(Request $request){
       try{
        
           
        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }



}