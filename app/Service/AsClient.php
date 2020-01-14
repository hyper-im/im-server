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

    public function __construct(StdoutLoggerInterface $stdoutLogger, ContainerInterface $container)
    {
        $this->logger = $stdoutLogger;
        $this->container = $container;
        $this->getInstance();
    }

    public function getInstance(){
        if($this->client == null){
            $this->initClient();
        }
        return $this;
    }

    public function initClient(){
        $this->logger->debug("正在实例化initClient.....");
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

        go(function() use ($im_router_ip, $im_router_port, $im_server){
            $cli = new Client($im_router_ip, $im_router_port);
            $ret = $cli->upgrade("/im-router");
            if ($ret) {
                $data=[
                    'serviceName'=>'IM-SERVER',
                    'ip'=>$im_server['ip'],
                    'port'=>$im_server['port']
                ];

                $cli->push(im_encode(
                    ClientCode::REGISTER_FROM_SERVER,
                    $data,
                    ClientCode::FROM_SERVER_CLIENT
                ));

                $rec = $cli->recv();
                try{
                    $data = json_decode($rec, true);
                    if($data['code'] == 200){
                        $this->client = $cli;
                        $this->logger->debug(sprintf('Connect Router Server Success.'));
                    }else{
                        $this->logger->debug(sprintf('Connect Router Server Fail.'));
                        throw new ServiceException('Can not connect Router Server!');
                    }
                }catch (\Exception $exception){
                    throw new ServiceException('Can not connect Router Server!');
                }

                //心跳处理
                swoole_timer_tick(3000,function ()use($cli){
                    if($cli->errCode==0){
                        $cli->push('',WEBSOCKET_OPCODE_PING); //
                    }
                });
            }
        });
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
