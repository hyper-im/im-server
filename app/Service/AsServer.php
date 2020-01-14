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

namespace App\Service;


use App\Constants\ClientCode;
use App\Constants\ServerCode;
use App\Ws\UserCollect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\WebSocketServer\Collector\FdCollector;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class AsServer
{
    /**
     * @Inject()
     * @var AsClient
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
        $this->client->broadCast(ClientCode::REGISTER_FROM_USER,$user,ClientCode::REGISTER_FROM_SERVER);

        $broadCast_data = [
            'data' => "欢迎新朋友:{$user['username']}!",
        ];

        //发给router,广播
        $this->client->broadCast(ClientCode::SERVER_CLIENT_BROADCAST,$broadCast_data);
        foreach (FdCollector::list() as $connection){
            if($fd != $connection){
                $server->push($fd, json_encode($broadCast_data));
            }
        }
    }

    public function message(WebSocketServer $server, Frame $frame){
        $fd = $frame->fd;
        $im_data = im_decode($frame->data);
        $action = $im_data['action'];
        $params = $im_data['params'];

        //将要push的信息
        $pushData['data'] = $params['data'];

        $fd_in_server = false;
        switch ($action){
            case ServerCode::CHAT_PRIVATE;
                //判断是否在当前服务器内
                $uid = UserCollect::getUidByFd($fd);
                if($uid){
                    $fd_in_server = true;
                    $server->push($fd, json_encode($pushData));
                }
                break;
            case ServerCode::CHAT_CHANNEL;
                //检测频道是否存在
                break;
            case ServerCode::CHAT_BROADCAST;
                $msg = "FD={$frame->fd}的大神说: ".$im_data['data'];
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

        if(!$fd_in_server){
            //进行广播信息
            $this->client->broadCast(ClientCode::SERVER_CLIENT_BROADCAST,'');
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
