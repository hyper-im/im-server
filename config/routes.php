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

use Hyperf\HttpServer\Router\Router;

Router::addServer('http', function () {
    Router::get('/user/register', 'App\Http\Im\UserController@register');
    Router::get('/user/login', 'App\Http\Im\UserController@login');
    Router::get('/user/logout', 'App\Http\Im\UserController@logout');
    Router::get('/cal', 'App\Controller\IndexController@test');
});

Router::addServer('ws', function () {
    Router::get('/im', 'App\Ws\ServerController');
});
