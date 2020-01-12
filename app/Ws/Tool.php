<?php
/**
 * +----------------------------------------------------------------------
 * |
 * +----------------------------------------------------------------------
 * | Copyright (c) 2020 https://github.com/hyper-im All rights reserved.
 * +----------------------------------------------------------------------
 * | Date：20-1-12 下午11:07
 * | Author: Bada (346025425@qq.com) QQ：346025425
 * +----------------------------------------------------------------------
 */

namespace App\Ws;


use Swoole\Http\Request;
use Swoole\WebSocket\Server as WebSocketServer;

class Tool
{
    //解析data得到数据
    public static function decode(Request $request){

        $data = $request->getData();
        var_dump($data);
//        $data['data'] = json_decode($data['data'], true);
//        $data['verify'] = true;
        if(!in_array($data['action'], ImActionType::PARAMS)){
            return false;
        }
        return $data;
    }

    //对返回给客户端的信息进行encode
    public static function encode($data){
        return json_encode($data);
    }

    public static function check_fd(WebSocketServer $server, $userId){
        //先获取userId对应的fd
        //直接从redis里查询. userId是否在其中

        $connections = $server->connections;
        var_dump($connections);
    }


    public function data_verify(){

}


}
