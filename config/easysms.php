<?php
/**
 * Created by PhpStorm.
 * author: _Dust_
 * Date: 2018-09-03
 * Time: 15:16
 */


return [

    'config' => [
        // HTTP 请求的超时时间（秒）
        'timeout' => 5.0,

        // 默认发送配置
        'default' => [
            // 网关调用策略，默认：顺序调用
            'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

            // 默认可用的发送网关
            'gateways' => [
                'aliyun',
            ],
        ],
        // 可用的网关配置
        'gateways' => [
            'errorlog' => [
                'file' => '/tmp/easy-sms.log',
            ],
            'aliyun' => [
                'access_key_id' => 'LTAI4bYV43jJ43pb',
                'access_key_secret' => 'gdyE435mhfTZ33JjZguboltUvD5HJF',
                'sign_name' => '艾艾语音',
            ],
        ],
    ],

    'tempLet_id' => [

        'revise' => 'SMS_143685233',
        'reg' => 'SMS_143685234'
    ],



];