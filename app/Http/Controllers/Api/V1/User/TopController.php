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
use App\Http\Resources\TopBanner;
use App\Http\Resources\TopList;
use App\Http\Resources\UserDetail;
use App\Models\Api\Banner;
use App\Models\Api\User;
use App\Models\Api\UserFacilitate;
use App\Models\Api\UserMsg;
use App\Models\Api\UserMutual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function PHPSTORM_META\type;

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
           $validator = \Validator::make($request->all(), [
               'to_user_id' => 'required',
           ],[
               'to_user_id.required' => 'to_user_id参数不能为空！',
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'error' => $validator->errors()->first()
               ]);
           }

           $user_id = auth()->id();
           $to_user_id = $request->to_user_id;
           $user=new User();
           $userDetail=$user->where('id',$to_user_id)
               ->with('photo')
               ->with('require')
               ->with('extend')
               ->first();

//           dd($userDetail->toArray());

           if (!$userDetail->toArray()) {
               //是否把对方加入心动
                $xd = UserMutual::where('user_id',$user_id)->where('to_user_id',$to_user_id)->first();
               //判断是否加入心动
               if($xd){
                   //判断是否互相加入心动
                   if($xd->is_hu == 1){
                       $userDetail->is_hu = true;
                   }else{
                       $userDetail->is_hu = false;
                       $userDetail->is_jia = true;
                   }

                   //判断是否已解锁联系方式
                   if($xd->is_lock == 1){
                       $userDetail->is_lock = true;
                   }else{
                       $userDetail->is_lock = false;
                   }

               }else{
                   $userDetail->is_hu = false;
                   $userDetail->is_lock = false;
                   $userDetail->is_jia = false;
                   $userDetail->phone='*******';
                   $userDetail->wx_no='*******';
                   $userDetail->qq='*******';
               }

               return $this->sendJson(200,'获取用户详细资料成功',UserDetail::make($userDetail));


           }else{
               $userDetail->phone='';
               $userDetail->wx_no='';
               $userDetail->is_hu=false;  //是否互相关注
               $userDetail->is_lock=false; //是否解锁联系方式
               $userDetail->is_jia = false; // 是否加对方为心动
               $userDetail->phone='*******';
               $userDetail->wx_no='*******';
               $userDetail->qq='*******';
               return $this->sendJson(200,'获取用户详细资料成功',UserDetail::make($userDetail));
           }
        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL3, $exception->getMessage());
       }
    
    }

    /**
     * 获取最新通知
     * @param Request $request
     * @return TopController|\Illuminate\Http\JsonResponse
     */
    public function getNotice(Request $request){
       try{

           $notice = self::_getConfig('Notice');

           return $this->sendJson(200,'获取成功',$notice?:'当前时间为：'.date('Y-m-d H:i:s'));

       }catch (\Exception $exception){

          return $this->sendError(Code::FAIL3, $exception->getMessage());
       }

    }

    /**
     * 获取banner
     * @param Request $request
     * @return TopController|\Illuminate\Http\JsonResponse
     */
    public function getBanner(Request $request){
       try{

           $list = Banner::where('type',2)->get();

           if($list->toArray()){

               return $this->sendJson(200,'获取成功',TopBanner::collection($list));
           }else{

               return $this->sendJson(200,'暂无数据',[]);
           }

       }catch (\Exception $exception){

          return $this->sendError(Code::FAIL, $exception->getMessage());
       }

    }

    /**
     * 红娘牵线
     * @param Request $request
     * @return TopController|\Illuminate\Http\JsonResponse
     */
    public function matchmaker(Request $request){
       try{
           $validator = \Validator::make($request->all(), [
               'to_user_id' => 'required',
               'content' => 'required',
           ],[
               'to_user_id.required' => 'to_user_id参数不能为空！',
               'content.required' => '说明内容不能为空！',
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'error' => $validator->errors()->first()
               ]);
           }

           $user_id=auth()->id();
           $to_user_id=$request->input('to_user_id');
           if($user_id==$to_user_id) {
               return $this->sendError(400, '不能申请自己哦！');
           }
           $yue=new UserFacilitate();
           $re1=$yue->where('user_id',$user_id)->where('to_user_id',$to_user_id)->first();
           if ($re1){
               return $this->sendError(400,'已申请过对该用户的牵线');
           }else{
               $yue->user_id=$user_id;
               $yue->to_user_id=$to_user_id;
               $yue->content=$request->input('content');
               if ($yue->save()) {
                   return $this->sendJson(200,'申请月老牵线成功！');
               }else{
                   return $this->sendError(Code::FAIL3,'申请月老牵线失败');
               }
           }
        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL3, $exception->getMessage());
       }
    
    }

    /**
     * 加入心动
     * @param Request $request
     * @param to_user_id
     * @return TopController|\Illuminate\Http\JsonResponse
     */
    public function joinPalpitation(Request $request){
       try{
           $validator = \Validator::make($request->all(), [
               'to_user_id' => 'required',
               'content' => 'required',
           ],[
               'to_user_id.required' => 'to_user_id参数不能为空！',
               'content.required' => '说明内容不能为空！',
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'error' => $validator->errors()->first()
               ]);
           }

           $user = auth()->user();
           $user_id=$user->id;
           $phone1=$user->phone;
           $to_user_id=$request->input('to_user_id');
           $content=$request->input('content');

           if($user_id==$to_user_id) {
               return $this->sendError(400, '自己不能加自己的心动哦！');
           }

           //检测用户是否为会员
            $vip = $this->is_vip($user_id);

            if(!$vip['status']){

                //不是会员则扣除 心动点数

                if($user->x_point){
                    \DB::table('user')->decrement('x_point',1);
                }else{
                    return $this->sendError(400,'心动点数不足,开通会员可获得无限心动哦');
                }
            }

           $join=new UserMutual();

           //先查看自己有没有加对方为心动
           $oneself = $join->where('user_id',$user_id)
               ->where('to_user_id',$to_user_id)
               ->first();

           if ($oneself){
               return $this->sendError(400,'该用户已加入心动');
           }else{
               //再查看对方有没有把我加为心动
               $others = $join->where('user_id',$to_user_id)
                   ->where('to_user_id',$user_id)
                   ->first();
               if ($others){
                   $join->user_id = $user_id;
                   $join->to_user_id = $to_user_id;
                   $join->is_hu = 1;
                   $join->content = $content;
                   if ($join->save()) {
                       //todo 互为心动 发短信通知对方
                       $to_user=\DB::table('user')->where('id',$to_user_id)->first();
                       $to_user->is_hu = 1;
                       if($to_user->save()){
                           $this->sendMsg($user_id,$to_user_id,'双方互相加入心动',2);
                           //$this->sendSms('17683961037','555888');
                           if($to_user->phone){
                               $this->send_Sms($to_user->phone,$user_id);
                           }
                           if($phone1){
                               $this->send_Sms($phone1,$to_user->id);
                           }

                       }
                       return $this->sendJson(200,'加入心动成功');
                   }
                   return $this->sendError(400,'加入心动失败');
               }else{
                   $join->user_id=$user_id;
                   $join->to_user_id=$to_user_id;
                   $join->content=$content;
                   if ($join->save()) {
                       $this->sendMsg($user_id,$to_user_id,'加入心动',2);
                       return $this->sendJson(200,'加入心动成功');
                   }else{
                       return $this->sendError(400,'加入心动失败');
                   }
               }

           }
        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 红娘牵线 | 加入心动 提示文字
     * @param Request $request
     * @return TopController|\Illuminate\Http\JsonResponse
     */
    public function explain(Request $request){
       try{

           switch ($request->type){

               case 'xd':
                   $data = self::_getConfig('XINDONG_TISHI');
                   break;
               case 'qx':
                   $data = self::_getConfig('HONG_QIAN');
                   break;
           }

           return $this->sendJson(200,'获取成功',$data);

       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 获取打赏解锁联系方式价格
     * @param Request $request
     * @return TopController|\Illuminate\Http\JsonResponse
     */
    public function getUnlockPrice(Request $request){
       try{

           $data['zuiDi'] = self::_getConfig('JIESUO_PRICE');

           $list = self::_getConfig('JIESUO_DATA');

           $data['price_list'] = explode(',',$list);

           return $this->sendJson(200,'获取成功',$data);
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }
    

    /**
     * 更新消息读取状态
     * @param Request $request
     * @param id 消息id
     * @return TopController|\Illuminate\Http\JsonResponse
     */
    public function msgRead(Request $request){
        try{
            $re=(new UserMsg())->where('id',$request->input('id'))->update(['is_red'=>1]);
            if ($re) {
                return $this->sendJson(200,'成功读消息！');
            }else{
                return $this->sendError(200,'读消息失败！');
            }
        }catch (\Exception $exception){
            return $this->sendError(Code::FAIL3,$exception->getMessage());
        }
    }


}