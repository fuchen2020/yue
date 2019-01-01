<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/12/23
 * Time: 22:45
 */

use Illuminate\Routing\Router as Router;
use App\Http\Controllers\Api\V1\User as User;

Route::group([], function (Router $api) {

    /**
     * 获取推荐列表
     * 访问地址: POST: getList
     * 控制器位置: App\Http\Controllers\API\User\TopController.php
     **/
    $api->match(['get','post'],'getList',User\TopController::class.'@getList');

    /**
     * 获取最新通知
     * 访问地址: POST: getNotice
     * 控制器位置: App\Http\Controllers\API\User\TopController.php
     **/
    $api->match(['get','post'],'getNotice',User\TopController::class.'@getNotice');

    /**
     * 获取Banner
     * 访问地址: POST: getBanner
     * 控制器位置: App\Http\Controllers\API\User\TopController.php
     **/
    $api->match(['get','post'],'getBanner',User\TopController::class.'@getBanner');

    /**
     * 获取红娘牵线 | 加入心动 提示文字
     * 访问地址: POST: explain
     * 控制器位置: App\Http\Controllers\API\User\TopController.php
     **/
    $api->match(['get','post'],'explain',User\TopController::class.'@explain');





});

//需要用户登录

Route::group(['middleware'=>'check.login'], function (Router $api) {

    /**
     * 获取用户详细资料
     * 访问地址: POST: getDetail
     * 控制器位置: App\Http\Controllers\API\User\TopController.php
     **/
    $api->match(['get','post'],'getDetail',User\TopController::class.'@getDetail');

    /**
     * 红娘牵线
     * 访问地址: POST: matchmaker
     * 控制器位置: App\Http\Controllers\API\User\TopController.php
     **/
    $api->match(['get','post'],'matchmaker',User\TopController::class.'@matchmaker');

    /**
     * 加入心动
     * 访问地址: POST: joinPalpitation
     * 控制器位置: App\Http\Controllers\API\User\TopController.php
     **/
    $api->match(['get','post'],'joinPalpitation',User\TopController::class.'@joinPalpitation');

    /**
     * 获取打赏解锁联系方式价格
     * 访问地址: POST: getUnlockPrice
     * 控制器位置: App\Http\Controllers\API\User\TopController.php
     **/
    $api->match(['get','post'],'getUnlockPrice',User\TopController::class.'@getUnlockPrice');


});
