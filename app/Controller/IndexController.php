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
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Router\Router;
use HyperfTest\Di\Stub\AnnotationCollector;

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
        $a = intval($this->request->input('a'));
        $b = intval($this->request->input('b'));
        return $this->calculator->calculate($a, $b);
    }

}
