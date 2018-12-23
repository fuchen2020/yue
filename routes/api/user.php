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

    // vip 相关

    $api->group(['prefix' => 'vip'],function (Router $api){

        /**
         * 支付回调
         * 访问地址: POST: payCallback
         * 控制器位置: App\Http\Controllers\API\User\VipController.php
         **/
        $api->match(['get','post'],'payCallback',User\VipController::class.'@payCallback');

    });

    // 新人指导相关

    $api->group(['prefix' => 'qa'],function (Router $api){

        /**
         * 获取新手引导列表
         * 访问地址: POST: getList
         * 控制器位置: App\Http\Controllers\API\User\NewGuideController.php
         **/
        $api->match(['get','post'],'getList',User\NewGuideController::class.'@getList');

        /**
         * 获取新手引导详情
         * 访问地址: POST: getDetail
         * 控制器位置: App\Http\Controllers\API\User\NewGuideController.php
         **/
        $api->match(['get','post'],'getDetail',User\NewGuideController::class.'@getDetail');

        /**
         * 获取用户协议
         * 访问地址: POST: getAgreement
         * 控制器位置: App\Http\Controllers\API\User\NewGuideController.php
         **/
        $api->match(['get','post'],'getAgreement',User\NewGuideController::class.'@getAgreement');

        /**
         * 获取联系方式图片
         * 访问地址: POST: getContactImg
         * 控制器位置: App\Http\Controllers\API\User\NewGuideController.php
         **/
        $api->match(['get','post'],'getContactImg',User\NewGuideController::class.'@getContactImg');



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

        /**
         * 获取择偶要求信息
         * 访问地址: POST: getAskFor
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'getAskFor',User\UserController::class.'@getAskFor');
        /**
         * 设置择偶要求信息
         * 访问地址: POST: setAskFor
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'setAskFor',User\UserController::class.'@setAskFor');

        /**
         * 获取家庭情况信息
         * 访问地址: POST: getFamily
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'getFamily',User\UserController::class.'@getFamily');

        /**
         * 修改家庭情况信息
         * 访问地址: POST: editFamily
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'editFamily',User\UserController::class.'@editFamily');

        /**
         * 修改隐身模式
         * 访问地址: POST: editHide
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'editHide',User\UserController::class.'@editHide');

        /**
         * 实名认证
         * 访问地址: POST: realName
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'realName',User\UserController::class.'@realName');

        /**
         * 学历认证
         * 访问地址: POST: degreeCertificate
         * 控制器位置: App\Http\Controllers\API\User\UserController.php
         **/
        $api->match(['get','post'],'degreeCertificate',User\UserController::class.'@degreeCertificate');


    });

    // vip 相关

    $api->group(['prefix' => 'vip'],function (Router $api){

        /**
         * 获取Vip数据列表
         * 访问地址: POST: getVipList
         * 控制器位置: App\Http\Controllers\API\User\VipController.php
         **/
        $api->match(['get','post'],'getVipList',User\VipController::class.'@getVipList');

        /**
         * 所有支付（统一下单）
         * 访问地址: POST: Pay
         * 控制器位置: App\Http\Controllers\API\User\VipController.php
         **/
        $api->match(['get','post'],'Pay',User\VipController::class.'@Pay');


    });

    //心动相关

    $api->group(['prefix' => 'love'],function (Router $api){

        /**
         * 查询心动点纪录
         * 访问地址: POST: getHeartPointList
         * 控制器位置: App\Http\Controllers\API\User\HeartController.php
         **/
        $api->match(['get','post'],'getHeartPointList',User\HeartController::class.'@getHeartPointList');

        /**
         * 查询当前心动点数量和认证情况
         * 访问地址: POST: getHeartCount
         * 控制器位置: App\Http\Controllers\API\User\HeartController.php
         **/
        $api->match(['get','post'],'getHeartCount',User\HeartController::class.'@getHeartCount');


    });


});