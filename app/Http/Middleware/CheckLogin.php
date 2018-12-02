<?php
/**
 * Created by PhpStorm.
 * author: _Dust_
 * Date: 2018-07-13
 * Time: 15:14
 */

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
class CheckLogin
{
    public function handle($request, Closure $next, $guard = null)
    {

        $is_user=auth()->user();
        if (empty($is_user)) {
            return response()->json(['msg' => '请重新登录','code'=>4001]);
        }else{
//            $user_id=$is_user->id;
//            if ($is_user->is_frozen==1){
//                return response()->json(['msg' => '账号已被封禁!原因:'.$is_user->remarks,'code'=>400]);
//            }
        }
//        //用户连续登陆记录
//        if($is_user->end_time){
//            //时间相同,今天已记录;
//            if(date('Y-m-d',$is_user->end_time)!=date('Y-m-d')){
//                $u=User::where('id',$is_user->id);
//                $u->increment('login_num');
//                $u->update(['end_time'=>time()]);
//            }
//        }else{
//            $u=User::where('id',$is_user->id);
//            $u->increment('login_num');
//            $u->update(['end_time'=>time()]);
//        }


        return $next($request);
    }

}