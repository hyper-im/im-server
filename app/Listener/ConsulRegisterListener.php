<?php

declare(strict_types=1);

namespace App\Listener;

use App\Constants\ClientCode;
use App\Exception\ServiceException;
use App\Service\AsClient;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Framework\Event\OnManagerStart;
use Hyperf\Redis\Redis;
use Hyperf\WebSocketClient\ClientFactory;
use Psr\Container\ContainerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Requests;

/**
 * @Listener
 */
class ConsulRegisterListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var StdoutLoggerInterface|mixed
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class
        ];
    }

    public function process(object $event)
    {
        $obj = $this->container->get(ClientFactory::class);
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

        $client = $obj->create($im_router_ip.":".$im_router_port.'/im-router', true);
        $data=[
            'serviceName'=>'IM-SERVER',
            'ip'=>$im_server['ip'],
            'port'=>$im_server['port']
        ];

        $client->push(im_encode(
            ClientCode::REGISTER_FROM_SERVER,
            $data,
            ClientCode::FROM_SERVER_CLIENT
        ));

        $data = $client->recv();

        echo "work-start后, 开始注册信息到consul########".PHP_EOL;

        $redis = $this->container->get(Redis::class);
        $cnt = $redis->del('user_bind_fd','fd_bind_user','user_on_room');
        $this->logger->info("清除redis-key成功,{$cnt}个");
    }

    public function getWsProvidePort(){
        $server = config('server');
        if(!is_array($server)){
            return false;
        }

        foreach ($server['servers'] as $ser){
            if($ser['name'] == 'ws'){
//                return $ser['im_server_protocol'].'://'.$ser['im_server_ip'].':'.$ser['port'];
                return [
                    'ip' => $ser['im_server_ip'],
                    'port' => $ser['port']
                ];
            }
        }
    }
}
