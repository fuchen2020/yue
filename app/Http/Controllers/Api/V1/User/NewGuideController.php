<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/12/20
 * Time: 21:46
 */

namespace App\Http\Controllers\Api\V1\User;


use App\Http\Components\Code;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\NewGuideList;
use App\Models\Api\NewGuide;
use Illuminate\Http\Request;

class NewGuideController extends BaseController
{

    /**
     * 获取新手引导列表
     * @param Request $request
     * @param page 页数
     * @param size 条数
     * @return NewGuideController|\Illuminate\Http\JsonResponse
     */
    public function getList(Request $request){
       try{

           $page = $request->page?:1;
           $size = $request->size?:10;

           $list = NewGuide::where('type',1)->forPage($page,$size)->get();

           if ($list) {
               return $this->sendJson(200,'获取成功！',NewGuideList::collection($list));
           }else{
               return $this->sendJson(200,'暂无数据！',[]);
           }

        
       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }

    /**
     * 获取新手引导详情
     * @param Request $request
     * @return NewGuideController|\Illuminate\Http\JsonResponse
     */
    public function getDetail(Request $request){
       try{

           $validator = \Validator::make($request->all(), [
               'id' => 'required',
           ],[
               'id.required' => 'id参数不能为空！',
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'error' => $validator->errors()->first()
               ]);
           }

           $data = NewGuide::find($request->id);

           return $this->sendJson(200,'获取成功！',NewGuideList::make($data));

       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }


    /**
     * 获取用户协议
     * @param Request $request
     * @return NewGuideController|\Illuminate\Http\JsonResponse
     */
    public function getAgreement(Request $request){
        try{

            $data = NewGuide::where('type',2)->first();

            if ($data) {
                return $this->sendJson(200,'获取成功！',NewGuideList::make($data));
            }else{
                return $this->sendJson(200,'暂无数据！',[]);
            }

        }catch (\Exception $exception){

            return $this->sendError(Code::FAIL, $exception->getMessage());
        }

    }

    /**
     * 获取联系方式图片
     * @param Request $request
     * @return NewGuideController|\Illuminate\Http\JsonResponse
     */
    public function getContactImg(Request $request){
       try{

           $img = self::_getConfig('CONTACTIMG');

           return $this->sendJson(200,'获取成功！',$img);

       }catch (\Exception $exception){
    
          return $this->sendError(Code::FAIL, $exception->getMessage());
       }
    
    }
    
}