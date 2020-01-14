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


use App\Constants\ClientCode;
use App\Constants\ServerCode;
use http\Client\Curl\User;
use Hyperf\Di\Annotation\Inject;
use Hyperf\WebSocketServer\Collector\FdCollector;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class ImServerController
{
    /**
     * @Inject()
     * @var ClientController
     */
    protected $client;

    /**
     * @Inject()
     * @var UserCollect
     */
    protected $userCollect;

    public function open(WebSocketServer $server, Request $request){
        $fd = $request->fd;

        $user = UserCollect::getUserByFd($fd);
        $this->userCollect->joinIm($fd,$user);

        //用户,注册到route
        $register = [
            'action' => ClientCode::REGISTER
        ];
        $this->client->broadCast($msg);

        $msg['data'] = "欢迎新朋友:{$user['username']}!";

        //发给router,广播
        $this->client->broadCast($msg);
        foreach (FdCollector::list() as $connection){
            if($fd != $connection){
                $server->push($fd, $msg['data']);
            }
        }
    }

    public function message(WebSocketServer $server, Frame $frame){
        $fd = $frame->fd;
        $imRequest = json_decode($frame->data, true);
        $action = $imRequest['action'];

        switch ($action){
            case ServerCode::CHAT_PRIVATE;
                UserCollect::
                $pushData['data'] = '私人聊天信息';
                $server->push($frame->fd, Tool::encode($pushData));
                break;
            case ServerCode::CHAT_CHANNEL;
                //检测频道是否存在
                break;
            case ServerCode::CHAT_BROADCAST;
                $msg = "FD={$frame->fd}的大神说: ".$imRequest['data'];
                foreach (FdCollector::list() as $fd){
                    if($fd == $frame->fd){
                        continue;
                    }
                    $server->push($fd, $msg);
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
