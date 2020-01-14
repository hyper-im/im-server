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
//        $this->client->broadCast(ClientCode::REGISTER_FROM_USER,$user,ClientCode::REGISTER_FROM_SERVER);

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

        $is_broadcast = true;
        switch ($action){
            case ServerCode::CHAT_PRIVATE;
                //判断是否在当前服务器内
                $uid = UserCollect::getUidByFd($fd);
                if($uid){
                    $is_broadcast = false;
                    $server->push($fd, json_encode($pushData));
                }
                break;
            case ServerCode::CHAT_CHANNEL;
                //检测频道是否存在
                $channel = $params['channel'];
                $fd_in_channel = $this->userCollect->getFdByChannel($channel);
                foreach ($fd_in_channel as $chan_fd){
//                    $server->isEstablished($fd);
                    if($chan_fd != $fd){
                        $server->push($chan_fd, json_encode($pushData));
                    }
                }
                break;
            case ServerCode::CHAT_BROADCAST;
                foreach (FdCollector::list() as $fd){
                    if($fd == $fd){
                        continue;
                    }
                    $server->push($fd, $pushData);
                }
                break;
            case ServerCode::SERVER_CLIENT_BROADCAST;
                //不需要广播
                $is_broadcast = false;

                //uid和channel
                $uid = $params['uid'];
                if($uid){
                    $fd = $this->userCollect->getFdByUid($uid);
                }
                $channel = $params['channel'];

                break;
            default:
                break;
        }

        if($is_broadcast){
            //进行广播信息
            $this->client->broadCast(ClientCode::SERVER_CLIENT_BROADCAST,$params);
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