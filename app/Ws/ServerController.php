<?php
/**
 * +----------------------------------------------------------------------
 * |
 * +----------------------------------------------------------------------
 * | Copyright (c) 2020 https://github.com/hyper-im All rights reserved.
 * +----------------------------------------------------------------------
 * | Date：20-1-13 下午7:13
 * | Author: Bada (346025425@qq.com) QQ：346025425
 * +----------------------------------------------------------------------
 */

namespace App\Ws;


use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\WebSocketServer\Collector\FdCollector;
use Hyperf\WebSocketServer\Exception\WebSocketHandeShakeException;
use Hyperf\WebSocketServer\Security;
use Hyperf\WebSocketServer\Server as HyperServer;
use Requests;
use Swoole\Http\Request;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Server;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class ServerController extends HyperServer implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     * @Inject()
     * @var ImServerController
     */
    protected $imServer;

    public function onHandShake(Request $request, SwooleResponse $response): void
    {

        try {
            $security = $this->container->get(Security::class);

            $psr7Request = $this->initRequest($request);
            $psr7Response = $this->initResponse($response);

            $this->logger->debug(sprintf('WebSocket: fd[%d] start a handshake request.', $request->fd));

            $key = $psr7Request->getHeaderLine(Security::SEC_WEBSOCKET_KEY);
            if ($security->isInvalidSecurityKey($key)) {
                throw new WebSocketHandeShakeException('sec-websocket-key is invalid!');
            }

            //加入自己的逻辑
            $protocol = $psr7Request->getHeaderLine(Security::SEC_WEBSOCKET_PROTOCOL);
            $url = "http://127.0.0.1:9501/im-server/user/verify_token?token=".$protocol;
            $data = json_decode(Requests::get($url), true);
            if ($data['code'] != 200) {
                throw new WebSocketHandeShakeException('sec-websocket-protocol is invalid!');
            }

            $fd = $request->fd;
            UserCollect::addUserByFd($fd, $data['data']);

            $psr7Request = $this->coreMiddleware->dispatch($psr7Request);
            /** @var Dispatched $dispatched */
            $dispatched = $psr7Request->getAttribute(Dispatched::class);
            $middlewares = $this->middlewares;
            if ($dispatched->isFound()) {
                $registedMiddlewares = MiddlewareManager::get($this->serverName, $dispatched->handler->route, $psr7Request->getMethod());
                $middlewares = array_merge($middlewares, $registedMiddlewares);
            }

            $psr7Response = $this->dispatcher->dispatch($psr7Request, $middlewares, $this->coreMiddleware);

            $class = $psr7Response->getAttribute('class');

            if (! empty($class)) {
                FdCollector::set($request->fd, $class);

                defer(function () use ($request, $class) {
                    $instance = $this->container->get($class);
                    if ($instance instanceof OnOpenInterface) {
                        $instance->onOpen($this->getServer(), $request);
                    }
                });
            }
        } catch (\Throwable $throwable) {
            // Delegate the exception to exception handler.
            $psr7Response = $this->exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
        } finally {
            // Send the Response to client.
            if (! $psr7Response || ! $psr7Response instanceof Psr7Response) {
                return;
            }
            $this->logger->debug(sprintf('WebSocket: token校验通过.', $request->fd));
            $psr7Response->send();
        }
    }

    public function onOpen(WebSocketServer $server, Request $request): void
    {
        var_dump($request);
        $this->imServer->open( $server,  $request);
    }

    public function onMessage(WebSocketServer $server, Frame $frame): void
    {
        $this->imServer->message( $server,  $frame);
    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        $this->imServer->close(  $server,  $fd,  $reactorId);
    }
}
