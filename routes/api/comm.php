<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/11/25
 * Time: 15:44
 */

use Illuminate\Routing\Router as Router;
use App\Http\Controllers\Api\V1\Comm as Comm;
use App\Http\Controllers\Api\V1\User as User;

Route::group([], function (Router $api) {

    /**
     * 获取背景图片
     * 访问地址: POST: getBackImage
     * 控制器位置: App\Http\Controllers\API\Comm\CommController.php
     **/
    $api->match(['get','post'],'getBackImage',Comm\CommController::class.'@getBackImage');


});

//需要用户登录
Route::group(['middleware'=>'check.login'], function (Router $api) {

    /**
     * 新增模板ID
     * 访问地址: POST: addFormID
     * 控制器位置: App\Http\Controllers\API\Comm\CommController.php
     **/
    $api->match(['get','post'],'addFormID',Comm\CommController::class.'@addFormID');

    /**
     * 审核用户头像
     * 访问地址: POST: setHead
     * 控制器位置: App\Http\Controllers\API\User\UserController.php
     **/
    $api->match(['get','post'],'setHead',User\UserController::class.'@setHead');

});