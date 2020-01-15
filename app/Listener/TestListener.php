<?php

declare(strict_types=1);

namespace App\Listener;

use App\Constants\ClientCode;
use App\Service\AsClient;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\WebSocketClient\ClientFactory;
use Psr\Container\ContainerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Requests;

/**
 * @Listener
 */
class TestListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [

        ];
    }

    public function process(object $event)
    {

        $obj = $this->container->get(ClientFactory::class);
        $client = $obj->create('127.0.0.1:9502/im-router', false);
        $data=[
            'serviceName'=>'IM-SERVER',
            'ip'=>'127.0.0.1',
            'port'=>9512
        ];

        $client->push(im_encode(
            ClientCode::REGISTER_FROM_SERVER,
            $data,
            ClientCode::FROM_SERVER_CLIENT
        ));

        $data = $client->recv();

        $this->container->get(AsClient::class)->client = $client;
        echo "work-start后, 开始注册信息到consul########".PHP_EOL;
        print_r($client);
        $acclient = $this->container->get(AsClient::class);

        print_r($acclient->client);

        echo "work-start后, 开始注册信息到consul##########".PHP_EOL;
//        $im_server = $this->getWsProvidePort();
//        if(!$im_server){
//            return true;
//        }
//
//        $headers = [];
//        $options = [];
//        $consul_uri = config('consul')['uri'];
//        try{
//            Requests::put($consul_uri."/v1/kv/im_server", $headers,json_encode($im_server), $options);
//        }catch (\Exception $e){
//            exit($e->getMessage());
//        }
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
