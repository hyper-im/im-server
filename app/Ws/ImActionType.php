<?php
/**
 * +----------------------------------------------------------------------
 * |
 * +----------------------------------------------------------------------
 * | Copyright (c) 2020 https://github.com/hyper-im All rights reserved.
 * +----------------------------------------------------------------------
 * | Date：20-1-12 下午10:51
 * | Author: Bada (346025425@qq.com) QQ：346025425
 * +----------------------------------------------------------------------
 */

namespace App\Ws;


class ImActionType
{
    //个人聊天
    const CHAT_PRIVATE = 'chat_private';

    //频道聊天
    const CHAT_CHANNEL = 'chat_channel';

    //群发
    const CHAT_BROADCAST = 'chat_broadcast';

    //登录
    const LOGIN = 'login';

    //退出
    const LOGOUT = 'logout';

    const PARAMS = [];


}
