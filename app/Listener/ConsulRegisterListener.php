<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Framework\Event\MainWorkerStart;
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class
        ];
    }

    public function process(object $event)
    {
        echo "work-start后, 开始注册信息到consul".PHP_EOL;
        $im_server = $this->getWsProvidePort();
        if(!$im_server){
            return true;
        }

        $headers = [];
        $options = [];
        $consul_uri = config('consul')['uri'];
        try{
            Requests::put($consul_uri."/v1/kv/im_server", $headers,json_encode($im_server), $options);
        }catch (\Exception $e){
            exit($e->getMessage());
        }
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
