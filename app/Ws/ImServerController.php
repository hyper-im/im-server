<?php
/**
 * +----------------------------------------------------------------------
 * |
 * +----------------------------------------------------------------------
 * | Copyright (c) 2020 https://github.com/hyper-im All rights reserved.
 * +----------------------------------------------------------------------
 * | Date：20-1-12 下午10:40
 * | Author: Bada (346025425@qq.com) QQ：346025425
 * +----------------------------------------------------------------------
 */

namespace App\Ws;


use Nette\Utils\Image;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class ImServerController
{

    public function open(WebSocketServer $server, Request $request){
        $msg['data'] = "欢迎fd={$request->fd}的大神, 上线啦!";
        foreach ($server->connections as $fd){
            if($fd == $request->fd){
                continue;
            }
            $server->push($fd, $msg['data']);
        }
    }

    public function message(WebSocketServer $server, Frame $frame){
        $imRequest = json_decode($frame->data, true);
        $action = $imRequest['action'];
        switch ($action){
            case ImActionType::CHAT_PRIVATE;
                //检测fd是否存在
//                Tool::check_fd($server, $imRequest['data']['userId']);
                $pushData['data'] = '私人聊天信息';
                $server->push($frame->fd, Tool::encode($pushData));
                break;
            case ImActionType::CHAT_CHANNEL;
                //检测频道是否存在
                break;
            case ImActionType::CHAT_BROADCAST;
                $msg['data'] = "FD={$frame->fd}的大神, 发送了一条广播信息!";
                $pushData = Tool::encode($msg);
                foreach ($server->connections as $fd){
                    if($fd == $frame->fd){
                        continue;
                    }
                    $server->push($fd, $pushData);
                }
                break;
            default:
                echo 11;
                break;
        }
    }

    public function close(Server $server, int $fd, int $reactorId){
        $msg = "fd={$fd}的大神, 下线啦!";
        foreach ($server->connections as $client_fd){
            if($fd == $client_fd){
                continue;
            }
            $server->push($client_fd, $msg);
        }
    }

}
