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


use App\Constants\ClientCode;
use App\Exception\ServiceException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Http\Client;

class ClientController
{
    /**
     * @var Client
     */
    public $client = null;
    protected $logger = null;
    protected $container = null;

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

        $cli = new Client($im_router_ip, $im_router_port);
        $ret = $cli->upgrade("/im-router");
        if ($ret) {
            $data=[
                'action'=>ClientCode::REGISTER,
                'serviceName'=>'IM-SERVER',
                'ip'=>$im_router_ip,
                'port'=>$im_router_port
            ];
            $cli->push(json_encode($data));
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
    }

    public function broadCast($data){
        $this->client->push($data);
    }
}
