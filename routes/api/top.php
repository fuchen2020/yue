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




});