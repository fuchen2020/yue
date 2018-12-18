<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/12/18
 * Time: 22:06
 */

namespace App\Http\Controllers\Api\V1\User;


use App\Http\Components\Code;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\VipList;
use App\Models\Api\UserAuth;
use App\Models\Api\UserOrder;
use App\Models\Api\Vip;
use EasyWeChat\Factory;
use Illuminate\Http\Request;

class VipController extends BaseController
{

    /**
     * 获取Vip数据列表
     * @param Request $request
     * @return VipController|\Illuminate\Http\JsonResponse
     */
    public function getVipList(Request $request ){
       try{

           $list = Vip::all();

           if ($list) {

               $data = [
                   'list' => VipList::collection($list),
                   'vip_explain' => self::_getConfig('VIP_EXPLAIN'),
               ];

               return $this->sendJson(200,'获取成功！',$data);

           }else{
               $data = [
                   'list' => new \stdClass(),
                   'vip_explain' => '',
               ];

               return $this->sendJson(200,'暂无数据！',$data);
           }


       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }


    /**
     * 所有支付（统一下单）
     * @param Request $request
     * @return VipController|\Illuminate\Http\JsonResponse
     */
    public function Pay(Request $request){
       try{

           $validator = \Validator::make($request->all(), [
               'type' => 'required',
               'money' => 'required',
           ],[
               'type.required' => '订单类型不能为空',
               'money.required' => '订单金额不能为空'
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'error' => $validator->errors()->first()
               ]);
           }

           if($request->type == 1){
               $validator = \Validator::make($request->all(), [
                   'vip_id' => 'required',
               ],[
                   'vip_id.required' => '会员参数不能为空',
               ]);

               if ($validator->fails()) {
                   return response()->json([
                       'code' => 400,
                       'error' => $validator->errors()->first()
                   ]);
               }
           }

           try{
               $config=Config('pay');
               $this->app =Factory::payment($config);
               $paytype='JSAPI';
               //存订单记录
               $user_id= auth()->id();
               $open_id= UserAuth::where('id',$user_id)->value('m_openid');
               $param = $request->all();
               $order_no=date('YmdHis').rand(100000,999999).$user_id;
               $order=new UserOrder();
               $order->type=$param['type'];
               $order->order_no=$order_no;
               $order->user_id=$user_id;
               $order->money=$param['money'];

               if($param['type'] == 1){
                   $order->reason='开通会员';
                   $order->vip_id = $param['vip_id'];
               }else{
                   $order->reason='解锁联系方式';
               }

               if ($order->save()){
                   $result = $this->app->order->unify([
                       'body' => '打赏-月老',
                       'out_trade_no' => $order_no,
                       'total_fee' => $param['money']*100,
                       'spbill_create_ip' => '123.12.12.123', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
                       'notify_url' => 'https://yl.chenziyong.vip/api/v1/vip/payCallback', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                       'trade_type' =>'JSAPI',
                       'openid' => $open_id,
                   ]);

                   if ($result['result_code'] == 'SUCCESS') {

                       $jssdk = $this->app->jssdk;

                       $prepayId = $result['prepay_id'];   //获取预支付id
                       if ($paytype == 'JSAPI') {
                           //JSSDK
                           $json = $jssdk->sdkConfig($prepayId);
                           return $this->sendJson(200,'订单创建成功',$json);
                       }
                   }else{
                       return $this->sendJson(Code::FAIL2,'订单创建失败',new \stdClass());
                   }

               }else{
                   return $this->sendJson(Code::FAIL2,'订单创建失败',new \stdClass());
               }

           }catch (\Exception $exception){
               return $this->sendJson(Code::FAIL3,$exception->getMessage());
           }

       }catch (\Exception $exception){

          return $this->sendError(Code::FAIL3, $exception->getMessage());
       }

    }

    /**
     * 支付回调
     * @param Request $request
     * @return VipController|\Illuminate\Http\JsonResponse
     */
    public function payCallback(Request $request){
       try{
           $config=Config('pay');
           $payment = Factory::payment($config);
           $this->app=$payment;
           $response = $payment->handlePaidNotify(function($message, $fail) use ($payment){
               $order=new UserOrder();
               $order=$order->where('order_no',$message['out_trade_no'])->first();
               // 如果订单不存在 或者 订单已经支付过了
               if (!$order || $order->pay_status==1) {
                   // 告诉微信，我已经处理完了，订单没找到，别再通知我了
                   return true;
               }
               //利用回调的商家本地单号去微信查询订单支付结果
               $payStatus = $payment->order->queryByOutTradeNumber($message['out_trade_no']);
               // 结果中 return_code 表示通信状态，不代表支付状态
               if ($payStatus['return_code'] == 'SUCCESS') {
                   // 用户是否支付成功
                   if ($payStatus[ 'result_code'] == 'SUCCESS') {
                       // 更新支付时间为当前时间
                       $order->pay_time = date('Y-m-d H:i:s');
                       $order->pay_status = 1;
                       // 保存更新订单
                       $order->save();

                       //处理订单关联表
                       if($order->type == 1){
                           //会员记录


                       }else{
                          // 打赏解锁


                       }

                   } elseif ($payStatus[ 'result_code']  == 'FAIL') {
                       // 用户支付失败
                       $order->pay_status = 2;
                       // 保存订单
                       $order->save();
                   }
               } else {
                   return $fail('通信失败，请稍后再通知我');
               }

               // 返回处理完成
               return true;
           });

           return $response;

       }catch (\Exception $exception){

          return $this->sendError(Code::FAIL, $exception->getMessage());
       }

    }
    
}