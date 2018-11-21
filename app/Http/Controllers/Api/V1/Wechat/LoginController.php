<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/11/21
 * Time: 22:42
 */

namespace App\Http\Controllers\Api\V1\Wechat;


use App\Http\Components\Code;
use App\Http\Controllers\Api\BaseController;

class LoginController extends BaseController
{

   public function login(){

       try{

        
       }catch (\Exception $exception){

           return $this->sendError(Code::FAIL, $exception->getMessage());
       }

   }





}