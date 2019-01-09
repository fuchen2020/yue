<?php

use Illuminate\Routing\Router as Router;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::get('/test', function (Request $request) {
//    dd(22222);
//});


Route::group(
    [
        'prefix' => 'v1',
    ],
    function (Router $api) {
        //用户相关
        $api->group(['prefix' => 'user'], function (Router $api) {
            require base_path('routes/api/user.php');
        });

        //公共
        $api->group(['prefix' => 'common'], function (Router $api) {
            require base_path('routes/api/comm.php');
        });

        //推荐
        $api->group(['prefix' => 'rec'], function (Router $api) {
            require base_path('routes/api/top.php');
        });

        //圈子
        $api->group(['prefix' => 'circle'], function (Router $api) {
            require base_path('routes/api/circle.php');
        });


    });