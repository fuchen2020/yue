<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/11/25
 * Time: 17:20
 */

namespace App\Http\Controllers\Api\V1\User;


use App\Http\Components\Code;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;

class UserController extends BaseController
{


    /**
     * 添加基础资料
     * @param Request $request
     * @return UserController|\Illuminate\Http\JsonResponse
     */
    public function baseInfo(Request $request){
        try{

            $head=[
              'image_base64'=>'data:image/png;base64,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',
            ];

            $da=json_encode($head);


            $re=self::_checkHead($da);

            dd($re);


        }catch (\Exception $exception){

            return $this->sendError(Code::FAIL, $exception->getMessage());
        }

    }

}