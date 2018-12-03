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
                'access_key_id' => 'LTAIYPPyJF92o3HQ',
                'access_key_secret' => 'wN9mqFdcnpLVy0Lt1spRO52pgRQi77',
                'sign_name' => '圆缘科技',
            ],
        ],
    ],

    'tempLet_id' => [

        'check' => 'SMS_152213146', //检测
        'pay' => 'SMS_143685234', //支付
        'huX' => 'SMS_143861585', //互选成功
    ],



];