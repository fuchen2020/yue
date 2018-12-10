<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/11/22
 * Time: 21:25
 */
use Illuminate\Routing\Router as Router;
use App\Http\Controllers\Api\V1\Wechat as WeChat;
use App\Http\Controllers\Api\V1\User as User;

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
         * 用户基础信息
         * 访问地址: POST: baseInfo
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'baseInfo',User\UserController::class.'@baseInfo');

        /**
         * 解密手机号
         * 访问地址: POST: decryptPhone
         * 控制器位置: App\Http\Controllers\API\WeChat\LoginController.php
         **/
        $api->match(['get','post'],'decryptPhone',WeChat\LoginController::class.'@decryptPhone');

        /**
         * 新增实名资料信息
         * 访问地址: POST: authorise
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'authorise',User\UserController::class.'@authorise');

        /**
         * 获取相册列表
         * 访问地址: POST: getPhotoList
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'getPhotoList',User\UserController::class.'@getPhotoList');

        /**
         * 添加相册照片
         * 访问地址: POST: addPhoto
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'addPhoto',User\UserController::class.'@addPhoto');

        /**
         * 删除相册照片
         * 访问地址: POST: delPhoto
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'delPhoto',User\UserController::class.'@delPhoto');

        /**
         * 获取随机内心独白
         * 访问地址: POST: getHeart
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'getHeart',User\UserController::class.'@getHeart');

        /**
         * 新增内心独白
         * 访问地址: POST: addHeart
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'addHeart',User\UserController::class.'@addHeart');
        /**
         * 获取自己的内心独白
         * 访问地址: POST: getMyHeart
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'getMyHeart',User\UserController::class.'@getMyHeart');

        /**
         * 获取基础资料(就是前面新增基础资料的信息)
         * 访问地址: POST: getBaseInfo
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'getBaseInfo',User\UserController::class.'@getBaseInfo');

        /**
         * 修改基础资料(字段和新增时不一样)
         * 访问地址: POST: editBaseInfo
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'editBaseInfo',User\UserController::class.'@editBaseInfo');





    });

});