<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Controller;

use App\JsonRpc\CalculatorService;
use App\Service\AsClient;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Router\Router;
use HyperfTest\Di\Stub\AnnotationCollector;

/**
 * Notes: 文件类注释说明
 * Class IndexController
 * @package App\Controller
 * @AutoController()
 */
class IndexController extends AbstractController
{
    /**
     * @Inject()
     * @var CalculatorService
     */
    protected $calculator;
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return \Hyperf\Di\Annotation\AnnotationCollector::list();
//        return [
//            'method' => $method,
//            'message' => "Hello {$user}.",
//        ];
    }

    public function test(){
        $asClient = $this->container->get(AsClient::class);
        $asClient->client->push('{"action":"server_client_broadcast","params":{"data":"\u6b22\u8fce\u65b0\u670b\u53cb:xiaosan!","fd":0,"uid":[],"ip_port":"127.0.0.1:9512"},"from":"server_client_broadcast"}');
        $data = $asClient->client->recv();
        var_dump($data);
    }

}
