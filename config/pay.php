<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/16 0016
 * Time: 22:14
 */

// 微信配置文件
return [
    // 必要配置
    'app_id'             => 'wx5d68fecad5b54475',
    'mch_id'             => '1523331941',
    'key'                => '76larbyv14224o4jfyx3y0zqpx7dcxxx',   // API 密钥

    // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
//    'cert_path'          => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
//    'key_path'           => 'path/to/your/key',      // XXX: 绝对路径！！！！

    'notify_url'         => 'https://yl.chenziyong.vip/api/v1/home/payCallback',     // 你也可以在下单时单独设置来想覆盖它

];