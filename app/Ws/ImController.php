<?php
/**
 * +----------------------------------------------------------------------
 * |
 * +----------------------------------------------------------------------
 * | Copyright (c) 2018 https://www.hyperpay.me All rights reserved.
 * +----------------------------------------------------------------------
 * | Date：20-1-10 上午12:28
 * | Author: Bada (346025425@qq.com) QQ：346025425
 * +----------------------------------------------------------------------
 */

declare(strict_types=1);

namespace App\Ws;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

/**
 * Notes: 文件类注释说明
 * Class WebSocketController
 * @package App\Controller
 * Im
 */
class ImController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{

    public function onOpen(WebSocketServer $server, Request $request): void
    {
        //建立连接,
        $server->push($request->fd, 'Opened');
    }

    public function onMessage(WebSocketServer $server, Frame $frame): void
    {
        $server->push($frame->fd, 'Recv: ' . $frame->data);
    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        var_dump($fd.'closed');
    }
}
