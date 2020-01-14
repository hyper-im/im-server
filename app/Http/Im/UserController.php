<?php
/**
 * +----------------------------------------------------------------------
 * |
 * +----------------------------------------------------------------------
 * | Copyright (c) 2018 https://www.hyperpay.me All rights reserved.
 * +----------------------------------------------------------------------
 * | Date：20-1-10 上午12:52
 * | Author: Bada (346025425@qq.com) QQ：346025425
 * +----------------------------------------------------------------------
 */

namespace App\Http\Im;


use App\Controller\AbstractController;
use App\Ws\ClientController;
use App\Ws\ServerController;

class UserController extends AbstractController
{
    /**
     * 注册
     */
    public function register(){
        return 'register';
    }

    /**
     * 登录, 成功后, 返回token和im-server信息
     */
    public function login(){

        //获取consul客户端
        $client_consul = $this->container->get(\Hyperf\Consul\KV::class);
        return 'login';
    }

    /**
     * 退出
     */
    public function logout()
    {
        /** @var ClientController $client */
        $client = $this->container->get(ClientController::class);
        $client->getInstance();

        var_dump($client->client);
    }
}
