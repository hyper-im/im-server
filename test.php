<?php
/**
 * +----------------------------------------------------------------------
 * |
 * +----------------------------------------------------------------------
 * | Copyright (c) 2018 https://www.hyperpay.me All rights reserved.
 * +----------------------------------------------------------------------
 * | Date：20-1-9 上午11:28
 * | Author: Bada (346025425@qq.com) QQ：346025425
 * +----------------------------------------------------------------------
 */

use App\Constants\ClientCode;

require "./vendor/autoload.php";

class client{
    public $client = null;
}

/** @var client $obj */
$obj = new client();
$cli = go(function () use (&$obj){

    $cli = $obj->client = new \Swoole\Coroutine\Http\Client("39.107.235.47", 9512);
    $obj->client->setHeaders(
        ['Sec-WebSocket-Protocol' => 'route']
    );
    $ret = $obj->client->upgrade("/im?from=im-router");
    var_dump($ret);
//    if ($ret) {
//        $data=[
//            'serviceName'=>'IM-SERVER',
//            'ip'=>'127.0.0.1',
//            'port'=>9512
//        ];
//
//        $obj->client->push(im_encode(
//            ClientCode::REGISTER_FROM_SERVER,
//            $data,
//            ClientCode::FROM_SERVER_CLIENT
//        ));
//        $rec = $obj->client->recv();
//        var_dump($rec);
//    }
//    return $cli;
});

//$data=[
//    'serviceName'=>'IM-SERVER',
//    'ip'=>'127.0.0.1',
//    'port'=>9512
//];
//
//$obj->client->push(im_encode(
//    ClientCode::REGISTER_FROM_SERVER,
//    $data,
//    ClientCode::FROM_SERVER_CLIENT
//));
//$rec = $obj->client->recv();
//var_dump($rec);
