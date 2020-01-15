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

namespace App\Service;


use App\Constants\ClientCode;
use App\Exception\ServiceException;
use App\Ws\UserCollect;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\WebSocketClient\ClientFactory;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Http\Client;
use Swoole\Http\Request;

class AsClient
{
    /**
     * @var Client
     */
    public $client = null;
    protected $logger = null;
    protected $container = null;
    protected $server_info = null;

    /**
     * @Inject
     * @var ClientFactory
     */
    protected $clientFactory;

    public function __construct(StdoutLoggerInterface $stdoutLogger, ContainerInterface $container)
    {
        $this->logger = $stdoutLogger;
        $this->container = $container;
//        $this->getInstance();
    }

    public function getInstance(){
        if($this->client == null){
            $this->initClient();
        }
    }

    public function initClient(){
        /** @var ConfigInterface $config */
        $config = $this->container->get(ConfigInterface::class);

        $im_router_ip = $config->get('im_router.ip','');
        $im_router_port = intval($config->get('im_router.port',0));
        $this->logger->debug(sprintf('try connect im-router %s:%d',$im_router_ip,$im_router_port));
        if(empty($im_router_ip) || empty($im_router_port)){
            throw new ServiceException('Please check config setting! file path: config/autoload/im_router.php');
        }

        $im_server = $this->getWsProvidePort();
        if(!$im_server){
            throw new ServiceException('Please check config setting! file path: config/autoload/server.php');
        }

        // 通过 ClientFactory 创建 Client 对象，创建出来的对象为短生命周期对象
        $uri = $im_router_ip.":".$im_router_port.'/im-router';
        $this->client = $this->clientFactory->create($uri,false);
        // 向 WebSocket 服务端发送消息
        $data=[
            'serviceName'=>'IM-SERVER',
            'ip'=>$im_server['ip'],
            'port'=>$im_server['port']
        ];

        $this->client->push(im_encode(
            ClientCode::REGISTER_FROM_SERVER,
            $data,
            ClientCode::FROM_SERVER_CLIENT
        ));
        // 获取服务端响应的消息，服务端需要通过 push 向本客户端的 fd 投递消息，才能获取；以下设置超时时间 2s，接收到的数据类型为 Frame 对象。
        /** @var Frame $msg */
        $rec = $this->client->recv(1);
        try{
            $data = json_decode($rec, true);
            if($data['code'] == 200){
                $this->logger->debug(sprintf('Connect Router Server Success.'));
            }else{
                $this->logger->debug(sprintf('Connect Router Server Fail.'));
                throw new ServiceException('Can not connect Router Server!');
            }
        }catch (\Exception $exception){
            throw new ServiceException('Can not connect Router Server!');
        }
    }

    /**
     * server-client, 广播消息
     * @param $action
     * @param $data
     * @param string $from
     */
    public function broadCast($action,$data,$from=ClientCode::SERVER_CLIENT_BROADCAST){

        $request = $this->container->get(Request::class);
        $fd = $request->fd;
        $data['fd'] = $fd;
        $data['uid'] = UserCollect::getUidByFd($fd);
        $data['ip_port'] = $this->server_info;
        $push_data = im_encode($action, $data, $from);
        var_dump($push_data);
        $this->client->push($push_data);
    }

    public function getWsProvidePort(){
        $server = config('server');
        if(!is_array($server)){
            return false;
        }

        foreach ($server['servers'] as $ser){
            if($ser['name'] == 'ws'){
                $this->server_info = $ser['im_server_ip'].":".$ser['port'];
                return [
                    'ip' => $ser['im_server_ip'],
                    'port' => $ser['port']
                ];
            }
        }

        return false;
    }
}
