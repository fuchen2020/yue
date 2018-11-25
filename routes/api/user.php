<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/11/22
 * Time: 21:25
 */
use Illuminate\Routing\Router as Router;
use App\Http\Controllers\Api\V1\Wechat as WeChat;

Route::group([], function (Router $api) {

    //用户账户相关
    $api->group(['prefix' => 'account'], function (Router $api) {

        /**
         * 微信小程序用户授权登录
         * 访问地址: POST: login
         * 控制器位置: App\Http\Controllers\API\WeChat\LoginController.php
         **/
        $api->middleware('ss')->match(['get','post'],'login',WeChat\LoginController::class.'@login');

    });


});


//需要用户登录

Route::group(['middleware'=>'check.login'], function (Router $api) {

    //用户账户相关
    $api->group(['prefix' => 'account'], function (Router $api) {

        /**
         * 微信小程序用户授权登录
         * 访问地址: POST: login
         * 控制器位置: App\Http\Controllers\API\WeChat\LoginController.php
         **/
        $api->match(['get','post'],'login',WeChat\LoginController::class.'@login');

    });

});