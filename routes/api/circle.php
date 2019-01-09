<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2019/1/8
 * Time: 21:28
 */
use Illuminate\Routing\Router as Router;
use App\Http\Controllers\Api\V1\User as User;

Route::group([], function (Router $api) {

    /**
     * 获取推荐列表
     * 访问地址: POST: getList
     * 控制器位置: App\Http\Controllers\API\User\CircleController.php
     **/
    $api->match(['get','post'],'getList',User\CircleController::class.'@getList');


});


//需要用户登录

Route::group(['middleware'=>'check.login'], function (Router $api) {

    /**
     * 获取圈子动态列表
     * 访问地址: POST: getCircleList
     * 控制器位置: App\Http\Controllers\API\User\CircleController.php
     **/
    $api->match(['get','post'],'getCircleList',User\CircleController::class.'@getCircleList');

    /**
     * 发布动态
     * 访问地址: POST: release
     * 控制器位置: App\Http\Controllers\API\User\CircleController.php
     **/
    $api->match(['get','post'],'release',User\CircleController::class.'@release');

    /**
     * 获取动态详情
     * 访问地址: POST: getDetail
     * 控制器位置: App\Http\Controllers\API\User\CircleController.php
     **/
    $api->match(['get','post'],'getDetail',User\CircleController::class.'@getDetail');

    /**
     * 动态评论
     * 访问地址: POST: comment
     * 控制器位置: App\Http\Controllers\API\User\CircleController.php
     **/
    $api->match(['get','post'],'comment',User\CircleController::class.'@comment');

    /**
     * 回复评论
     * 访问地址: POST: reply
     * 控制器位置: App\Http\Controllers\API\User\CircleController.php
     **/
    $api->match(['get','post'],'reply',User\CircleController::class.'@reply');

    /**
     * 动态点赞
     * 访问地址: POST: praise
     * 控制器位置: App\Http\Controllers\API\User\CircleController.php
     **/
    $api->match(['get','post'],'praise',User\CircleController::class.'@praise');

    /**
     * 获取动态评论列表
     * 访问地址: POST: getComment
     * 控制器位置: App\Http\Controllers\API\User\CircleController.php
     **/
    $api->match(['get','post'],'getComment',User\CircleController::class.'@getComment');
    /**
     * 动态分享
     * 访问地址: POST: share
     * 控制器位置: App\Http\Controllers\API\User\CircleController.php
     **/
    $api->match(['get','post'],'share',User\CircleController::class.'@share');

});