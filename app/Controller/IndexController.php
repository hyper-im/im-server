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

use Hyperf\HttpServer\Router\Router;
use HyperfTest\Di\Stub\AnnotationCollector;

class IndexController extends AbstractController
{
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
}
