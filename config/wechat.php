<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/11/25
 * Time: 14:57
 */

return [

    'config' => [
        'app_id' => 'wx939c361c3d834620',
        'secret' => '53f3da60748024064fb053f32ecfcf8c',

        // 下面为可选项
        // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
        'response_type' => 'array',
        'log' => [
            'level' => 'debug',
            'file' => __DIR__ . '/mini.log',
        ],
    ],
];

